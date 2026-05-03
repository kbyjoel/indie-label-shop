<?php

declare(strict_types=1);

namespace App\Component\Mail\MessageHandler;

use App\Component\Download\DownloadTokenManager;
use App\Component\Mail\Message\SendDownloadReadyMessage;
use App\Entity\DownloadToken;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendDownloadReadyHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly DownloadTokenManager $downloadTokenManager,
        private readonly string $mailerFrom,
    ) {
    }

    public function __invoke(SendDownloadReadyMessage $message): void
    {
        $token = $this->em->find(DownloadToken::class, $message->getDownloadTokenId());
        if (!$token instanceof DownloadToken) {
            return;
        }

        $orderItem = $token->getOrderItem();
        $order = $orderItem->getOrder();
        if (!$order instanceof Order) {
            return;
        }

        $customer = $order->getCustomer();
        if (null === $customer || null === $customer->getEmail()) {
            return;
        }

        $signedUrl = $this->downloadTokenManager->refreshSignedUrl($token);

        $email = (new TemplatedEmail())
            ->from($this->mailerFrom)
            ->to((string) $customer->getEmail())
            ->subject('Vos fichiers sont prêts — commande #' . $order->getNumber())
            ->htmlTemplate('emails/download_ready.html.twig')
            ->textTemplate('emails/download_ready.txt.twig')
            ->context([
                'signedUrl' => $signedUrl,
                'expiresAt' => $token->getExpiresAt(),
                'orderNumber' => $order->getNumber(),
                'format' => $token->getFormat(),
            ])
        ;

        $this->mailer->send($email);
    }
}
