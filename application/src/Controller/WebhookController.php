<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\PaymentMethod;
use App\Payment\Gateway\PaypalGateway;
use App\Payment\PaymentProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PaymentProcessor $paymentProcessor,
        private readonly HttpClientInterface $httpClient,
        #[Autowire(env: 'STRIPE_WEBHOOK_SECRET')]
        private readonly string $stripeWebhookSecret,
        #[Autowire(env: 'PAYPAL_WEBHOOK_ID')]
        private readonly string $paypalWebhookId,
    ) {
    }

    #[Route('/webhook/stripe', name: 'webhook_stripe', methods: ['POST'])]
    public function stripe(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $this->stripeWebhookSecret);
        } catch (SignatureVerificationException) {
            return new Response('Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        if (in_array($event->type, ['payment_intent.succeeded', 'payment_intent.payment_failed'], true)) {
            $intentId = $event->data->object->id;
            $payment = $this->findPaymentByDetail('stripe_payment_intent_id', (string) $intentId);

            if (null !== $payment) {
                if ('payment_intent.succeeded' === $event->type) {
                    $this->paymentProcessor->complete($payment);
                } else {
                    $this->paymentProcessor->fail($payment);
                }
            }
        }

        return new Response('OK');
    }

    #[Route('/webhook/paypal', name: 'webhook_paypal', methods: ['POST'])]
    public function paypal(Request $request): Response
    {
        $body = $request->getContent();

        /** @var array<string, string[]> $rawHeaders */
        $rawHeaders = $request->headers->all();
        /** @var array<string, string> $flatHeaders */
        $flatHeaders = array_map(static fn (array $v): string => $v[0] ?? '', $rawHeaders);

        // Résoudre le gateway PayPal depuis la méthode de paiement active
        /** @var PaymentMethod|null $paymentMethod */
        $paymentMethod = $this->em->getRepository(PaymentMethod::class)
            ->findOneBy(['gatewayType' => 'paypal', 'enabled' => true]);

        if (null === $paymentMethod) {
            return new Response('No PayPal method configured', Response::HTTP_BAD_REQUEST);
        }

        $gateway = new PaypalGateway($paymentMethod, $this->httpClient);
        if (!$gateway->verifyWebhook($flatHeaders, $body, $this->paypalWebhookId)) {
            return new Response('Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($body, true) ?? [];
        $eventType = (string) ($data['event_type'] ?? '');
        $resourceId = (string) ($data['resource']['id'] ?? '');

        $payment = $this->findPaymentByDetail('paypal_order_id', $resourceId);

        if (null !== $payment) {
            if ('PAYMENT.CAPTURE.COMPLETED' === $eventType) {
                $this->paymentProcessor->complete($payment);
            } elseif ('PAYMENT.CAPTURE.DENIED' === $eventType) {
                $this->paymentProcessor->fail($payment);
            }
        }

        return new Response('OK');
    }

    private function findPaymentByDetail(string $key, string $value): ?Payment
    {
        $conn = $this->em->getConnection();
        $id = $conn->fetchOne(
            'SELECT id FROM sylius_payment WHERE JSON_UNQUOTE(JSON_EXTRACT(details, ?)) = ?',
            ['$.' . $key, $value],
        );

        if (!$id) {
            return null;
        }

        return $this->em->find(Payment::class, (int) $id);
    }
}
