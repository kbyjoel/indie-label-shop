<?php

declare(strict_types=1);

namespace App\Component\Payment;

use App\Component\Mail\Message\SendOrderConfirmedMessage;
use App\Component\Payment\Gateway\PaypalGateway;
use App\Component\Payment\Gateway\StripeGateway;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\PaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaymentProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $httpClient,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * Crée le Payment, appelle gateway->initiate() et stocke les details.
     * Idempotent : réutilise le Payment non-failed existant.
     */
    public function initiate(Order $order, PaymentMethod $paymentMethod): Payment
    {
        // Réutiliser un paiement existant non-échoué (idempotence back-button)
        foreach ($order->getPayments() as $existing) {
            if (!$existing instanceof Payment) {
                continue;
            }
            if (Payment::STATE_FAILED !== $existing->getState()) {
                return $existing;
            }
        }

        if (null === $order->getNumber()) {
            $order->setNumber('ORD-' . strtoupper(substr(uniqid(), -8)));
        }

        if (Order::STATE_CART === $order->getState()) {
            $order->setState(Order::STATE_NEW);
        }

        $payment = new Payment();
        $payment->setMethod($paymentMethod);
        $payment->setAmount($order->getTotal());
        $payment->setCurrencyCode($order->getCurrencyCode() ?? 'EUR');
        $payment->setState(Payment::STATE_NEW);
        $order->addPayment($payment);

        $gateway = $this->resolveGateway($paymentMethod);
        $details = $gateway->initiate($payment->getAmount() ?? 0, $payment->getCurrencyCode() ?? 'EUR');
        $payment->setDetails($details);

        $this->em->flush();

        return $payment;
    }

    /**
     * Passe le Payment en COMPLETED et la commande en FULFILLED.
     * Idempotent : no-op si déjà COMPLETED.
     */
    public function complete(Payment $payment): void
    {
        if (Payment::STATE_COMPLETED === $payment->getState()) {
            return;
        }

        $payment->setState(Payment::STATE_COMPLETED);

        $order = $payment->getOrder();
        if (null !== $order) {
            $order->setState(Order::STATE_FULFILLED);
        }

        $this->em->flush();

        if (null !== $order) {
            $orderId = $order->getId();
            if (null !== $orderId) {
                $this->messageBus->dispatch(new SendOrderConfirmedMessage($orderId));
            }
        }
    }

    /**
     * Passe le Payment en FAILED.
     * Idempotent : no-op si déjà FAILED.
     */
    public function fail(Payment $payment): void
    {
        if (Payment::STATE_FAILED === $payment->getState()) {
            return;
        }

        $payment->setState(Payment::STATE_FAILED);
        $this->em->flush();
    }

    public function resolveGateway(PaymentMethod $paymentMethod): StripeGateway|PaypalGateway
    {
        return match ($paymentMethod->getGatewayType()) {
            'stripe' => new StripeGateway($paymentMethod),
            'paypal' => new PaypalGateway($paymentMethod, $this->httpClient),
            default => throw new \RuntimeException(\sprintf('Unknown gateway type: %s', $paymentMethod->getGatewayType())),
        };
    }
}
