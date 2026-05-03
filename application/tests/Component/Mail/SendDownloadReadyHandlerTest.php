<?php

declare(strict_types=1);

namespace App\Tests\Component\Mail;

use App\Component\Download\DownloadTokenManager;
use App\Component\Mail\Message\SendDownloadReadyMessage;
use App\Component\Mail\MessageHandler\SendDownloadReadyHandler;
use App\Entity\DownloadToken;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;

class SendDownloadReadyHandlerTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private MailerInterface&MockObject $mailer;
    private DownloadTokenManager&MockObject $tokenManager;
    private SendDownloadReadyHandler $handler;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->tokenManager = $this->createMock(DownloadTokenManager::class);

        $this->handler = new SendDownloadReadyHandler(
            $this->em,
            $this->mailer,
            $this->tokenManager,
            'noreply@test.com',
        );
    }

    public function testSendsEmailWithSignedUrl(): void
    {
        $signedUrl = 'https://s3.example.com/download?token=abc';

        $customer = $this->createStub(\Sylius\Component\Core\Model\CustomerInterface::class);
        $customer->method('getEmail')->willReturn('buyer@example.com');

        $order = $this->createStub(Order::class);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getNumber')->willReturn('ORD-00001');

        $orderItem = $this->createStub(OrderItem::class);
        $orderItem->method('getOrder')->willReturn($order);

        $token = $this->createStub(DownloadToken::class);
        $token->method('getOrderItem')->willReturn($orderItem);
        $token->method('getFormat')->willReturn('mp3');
        $token->method('getExpiresAt')->willReturn(new \DateTimeImmutable('+24 hours'));

        $this->em->expects(self::once())
            ->method('find')
            ->with(DownloadToken::class, 7)
            ->willReturn($token)
        ;

        $this->tokenManager->expects(self::once())
            ->method('refreshSignedUrl')
            ->with($token)
            ->willReturn($signedUrl)
        ;

        $this->mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(function ($email) use ($signedUrl) {
                $context = $email->getContext();

                return isset($context['signedUrl']) && $context['signedUrl'] === $signedUrl;
            }))
        ;

        ($this->handler)(new SendDownloadReadyMessage(7));
    }

    public function testSkipsWhenTokenNotFound(): void
    {
        $this->em->expects(self::once())
            ->method('find')
            ->willReturn(null)
        ;

        $this->tokenManager->expects(self::never())
            ->method('refreshSignedUrl')
        ;

        $this->mailer->expects(self::never())
            ->method('send')
        ;

        ($this->handler)(new SendDownloadReadyMessage(99));
    }
}
