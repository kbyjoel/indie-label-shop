<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Download\DownloadTokenManager;
use App\Component\Download\Message\GenerateDownloadMessage;
use App\Entity\DownloadToken;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Release;
use App\Entity\ShopUser;
use App\Repository\DownloadTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class DownloadController extends AbstractController
{
    public function __construct(
        private readonly DownloadTokenManager $tokenManager,
        private readonly DownloadTokenRepository $tokenRepository,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
        private readonly FilesystemOperator $privateStorage,
        private readonly string $kernelEnvironment,
    ) {
    }

    #[Route('/download/prepare', name: 'front_download_prepare', methods: ['POST'])]
    public function prepare(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $data = json_decode((string) $request->getContent(), true);
        $orderItemId = (int) ($data['orderItemId'] ?? 0);
        $format = (string) ($data['format'] ?? '');

        if (!\in_array($format, ['mp3', 'wav', 'zip'], true)) {
            return new JsonResponse(['error' => 'Invalid format'], 400);
        }

        $orderItem = $this->em->find(OrderItem::class, $orderItemId);
        if (!$orderItem) {
            throw $this->createNotFoundException();
        }

        /** @var ShopUser $shopUser */
        $shopUser = $this->getUser();
        $customer = $shopUser->getCustomer();

        $order = $orderItem->getOrder();
        if (!$order instanceof Order) {
            throw $this->createNotFoundException();
        }

        if ($order->getCustomer() !== $customer) {
            throw $this->createAccessDeniedException();
        }

        if (!\in_array($order->getState(), [OrderInterface::STATE_FULFILLED, 'completed'], true)) {
            throw $this->createAccessDeniedException('Order is not fulfilled');
        }

        $variant = $orderItem->getVariant();
        if (!$variant instanceof Release || !$variant->getMedia()?->isDigital()) {
            throw $this->createAccessDeniedException('Item is not a digital download');
        }

        $token = $this->tokenManager->createOrReuse($orderItem, $format, $this->em);

        if ($token->isValid()) {
            return new JsonResponse([
                'status' => DownloadToken::STATUS_READY,
                'token' => $token->getTokenValue(),
                'url' => $this->tokenManager->getDownloadUrl($token),
            ]);
        }

        /** @var int $orderItemId */
        $orderItemId = $orderItem->getId();
        /** @var int $tokenId */
        $tokenId = $token->getId();

        $this->messageBus->dispatch(new GenerateDownloadMessage(
            $orderItemId,
            $format,
            $tokenId,
        ));

        return new JsonResponse([
            'status' => DownloadToken::STATUS_PENDING,
            'token' => $token->getTokenValue(),
        ], 202);
    }

    #[Route('/download/status/{tokenValue}', name: 'front_download_status', methods: ['GET'])]
    public function status(string $tokenValue): JsonResponse
    {
        $token = $this->tokenRepository->findByTokenValue($tokenValue);
        if (!$token) {
            throw $this->createNotFoundException();
        }

        if (DownloadToken::STATUS_READY === $token->getStatus()) {
            return new JsonResponse([
                'status' => DownloadToken::STATUS_READY,
                'url' => $this->tokenManager->getDownloadUrl($token),
            ]);
        }

        if (DownloadToken::STATUS_FAILED === $token->getStatus()) {
            return new JsonResponse([
                'status' => DownloadToken::STATUS_FAILED,
                'message' => 'La génération du fichier a échoué.',
            ]);
        }

        return new JsonResponse(['status' => $token->getStatus()]);
    }

    #[Route('/download/file/{tokenValue}', name: 'front_download_file', methods: ['GET'])]
    public function serveFile(string $tokenValue): StreamedResponse
    {
        if ('prod' === $this->kernelEnvironment) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        $token = $this->tokenRepository->findByTokenValue($tokenValue);
        if (!$token || DownloadToken::STATUS_READY !== $token->getStatus()) {
            throw $this->createNotFoundException();
        }

        /** @var ShopUser $shopUser */
        $shopUser = $this->getUser();
        $tokenOrder = $token->getOrderItem()->getOrder();
        if (!$tokenOrder instanceof Order || $tokenOrder->getCustomer() !== $shopUser->getCustomer()) {
            throw $this->createAccessDeniedException();
        }

        $storagePath = $token->getS3Path();
        if (!$storagePath) {
            throw $this->createNotFoundException();
        }

        $filename = basename($storagePath);

        return new StreamedResponse(function () use ($storagePath): void {
            $stream = $this->privateStorage->readStream($storagePath);
            while (!feof($stream)) {
                echo fread($stream, 8192);
                flush();
            }
            fclose($stream);
        }, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => \sprintf('attachment; filename="%s"', $filename),
        ]);
    }
}
