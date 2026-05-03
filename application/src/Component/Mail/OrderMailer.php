<?php

declare(strict_types=1);

namespace App\Component\Mail;

use App\Component\Mail\Message\SendDownloadReadyMessage;
use App\Component\Mail\Message\SendOrderConfirmedMessage;
use App\Entity\DownloadToken;
use App\Entity\Order;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderMailer
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function sendOrderConfirmed(Order $order): void
    {
        $orderId = $order->getId();
        if (null === $orderId) {
            return;
        }

        $this->messageBus->dispatch(new SendOrderConfirmedMessage($orderId));
    }

    public function sendDownloadReady(DownloadToken $token): void
    {
        $tokenId = $token->getId();
        if (null === $tokenId) {
            return;
        }

        $this->messageBus->dispatch(new SendDownloadReadyMessage($tokenId));
    }
}
