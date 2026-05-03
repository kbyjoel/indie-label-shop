<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Cart\CartContext;
use App\Component\Payment\Gateway\PaypalGateway;
use App\Component\Payment\Gateway\StripeGateway;
use App\Component\Payment\PaymentProcessor;
use App\Component\Shipment\ShippingCalculator;
use App\Entity\Address;
use App\Entity\Adjustment;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\PaymentMethod;
use App\Entity\Shipment;
use App\Entity\ShippingMethod;
use App\Entity\ShopUser;
use App\Form\Front\CheckoutAddressType;
use App\Form\Front\CheckoutShipmentType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    public function __construct(
        private CartContext $cartContext,
        private EntityManagerInterface $em,
        private ShippingCalculator $shippingCalculator,
        private PaymentProcessor $paymentProcessor,
    ) {
    }

    #[Route('/commande/adresse', name: 'front_checkout_address', methods: ['GET', 'POST'])]
    public function address(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->cartContext->getCart($request);
        if ($cart->getItems()->isEmpty()) {
            return $this->redirectToRoute('front_cart_index');
        }

        /** @var ShopUser $shopUser */
        $shopUser = $this->getUser();
        $cart->setCustomer($shopUser->getCustomer());

        $address = $cart->getShippingAddress() ?? new Address();
        $form = $this->createForm(CheckoutAddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cart->setShippingAddress($address);
            $billing = $this->cloneAddress($address);
            $this->em->persist($billing);
            $cart->setBillingAddress($billing);
            $this->em->flush();

            return $this->redirectToRoute('front_checkout_shipment');
        }

        return $this->render('front/checkout/address.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    #[Route('/commande/livraison', name: 'front_checkout_shipment', methods: ['GET', 'POST'])]
    public function shipment(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->cartContext->getCart($request);
        if ($cart->getItems()->isEmpty()) {
            return $this->redirectToRoute('front_cart_index');
        }
        if (null === $cart->getShippingAddress()) {
            return $this->redirectToRoute('front_checkout_address');
        }

        $form = $this->createForm(CheckoutShipmentType::class, null, ['cart' => $cart]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ShippingMethod $method */
            $method = $form->getData()['shippingMethod'];
            $amount = $this->shippingCalculator->computeAmount($method, $cart);

            // Remove previous shipments and shipping adjustments
            foreach ($cart->getShipments() as $oldShipment) {
                $cart->removeShipment($oldShipment);
                $this->em->remove($oldShipment);
            }
            $cart->removeAdjustments(AdjustmentInterface::SHIPPING_ADJUSTMENT);

            $shipment = new Shipment();
            $shipment->setMethod($method);
            $shipment->setState('ready');
            $cart->addShipment($shipment);

            $adjustment = new Adjustment();
            $adjustment->setType(AdjustmentInterface::SHIPPING_ADJUSTMENT);
            $adjustment->setLabel($method->getName() ?? '');
            $adjustment->setAmount($amount);
            $cart->addAdjustment($adjustment);

            $cart->recalculateItemsTotal();
            $this->em->flush();

            return $this->redirectToRoute('front_checkout_payment');
        }

        return $this->render('front/checkout/shipment.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    #[Route('/commande/paiement', name: 'front_checkout_payment', methods: ['GET'])]
    public function payment(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->cartContext->getCart($request);
        if ($cart->getItems()->isEmpty()) {
            return $this->redirectToRoute('front_cart_index');
        }
        if (null === $cart->getShippingAddress()) {
            return $this->redirectToRoute('front_checkout_address');
        }
        if ($cart->getShipments()->isEmpty()) {
            return $this->redirectToRoute('front_checkout_shipment');
        }

        $paymentMethods = $this->em->getRepository(PaymentMethod::class)->findBy(['enabled' => true]);

        return $this->render('front/checkout/payment.html.twig', [
            'cart' => $cart,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Initie le paiement : crée le Payment, appelle le gateway, retourne clientSecret/clientId.
     * Appelé en AJAX par le JS de la page de paiement lors de la sélection d'une méthode.
     */
    #[Route('/commande/paiement/initier', name: 'front_checkout_payment_initiate', methods: ['POST'])]
    public function paymentInitiate(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $data = json_decode($request->getContent(), true) ?? [];
        $paymentMethodId = (int) ($data['paymentMethodId'] ?? 0);

        $cart = $this->cartContext->getCart($request);
        if ($cart->getItems()->isEmpty()) {
            return $this->json(['error' => 'cart_empty'], 400);
        }

        /** @var PaymentMethod|null $paymentMethod */
        $paymentMethod = $this->em->find(PaymentMethod::class, $paymentMethodId);
        if (null === $paymentMethod || !$paymentMethod->isEnabled()) {
            return $this->json(['error' => 'invalid_payment_method'], 400);
        }

        $payment = $this->paymentProcessor->initiate($cart, $paymentMethod);
        $details = $payment->getDetails();

        return match ($paymentMethod->getGatewayType()) {
            'stripe' => (function () use ($paymentMethod, $details): JsonResponse {
                /** @var StripeGateway $gateway */
                $gateway = $this->paymentProcessor->resolveGateway($paymentMethod);

                return $this->json([
                    'gatewayType' => 'stripe',
                    'publishableKey' => $gateway->getPublishableKey(),
                    'clientSecret' => $details['stripe_client_secret'] ?? '',
                ]);
            })(),
            'paypal' => $this->json([
                'gatewayType' => 'paypal',
                'clientId' => $details['paypal_client_id'] ?? '',
            ]),
            default => $this->json(['error' => 'unsupported_gateway'], 400),
        };
    }

    /**
     * Confirmation côté serveur après stripe.confirmCardPayment().
     * Appelé par stripe_payment_controller.js.
     */
    #[Route('/commande/paiement/confirmer', name: 'front_checkout_payment_confirm', methods: ['POST'])]
    public function paymentConfirm(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $data = json_decode($request->getContent(), true) ?? [];
        $paymentIntentId = (string) ($data['paymentIntentId'] ?? '');

        $cart = $this->cartContext->getCart($request);

        $payment = null;
        foreach ($cart->getPayments() as $p) {
            if (!$p instanceof Payment) {
                continue;
            }
            if (($p->getDetails()['stripe_payment_intent_id'] ?? '') === $paymentIntentId) {
                $payment = $p;

                break;
            }
        }

        if (null === $payment) {
            return $this->json(['error' => 'payment_not_found'], 404);
        }

        $paymentMethod = $payment->getMethod();
        if (!$paymentMethod instanceof PaymentMethod) {
            return $this->json(['error' => 'invalid_payment_method'], 400);
        }

        /** @var StripeGateway $gateway */
        $gateway = $this->paymentProcessor->resolveGateway($paymentMethod);

        if (!$gateway->verify($payment->getDetails())) {
            $this->paymentProcessor->fail($payment);

            return $this->json(['error' => 'payment_not_succeeded'], 400);
        }

        $this->paymentProcessor->complete($payment);
        $request->getSession()->remove('_cart_token');

        return $this->json([
            'success' => true,
            'redirectUrl' => $this->generateUrl('front_checkout_success', ['number' => $cart->getNumber()]),
        ]);
    }

    /**
     * Crée une commande PayPal. Appelé par paypal_payment_controller.js (createOrder callback).
     */
    #[Route('/commande/paypal/creer', name: 'front_checkout_paypal_create_order', methods: ['POST'])]
    public function paypalCreateOrder(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->cartContext->getCart($request);
        if ($cart->getItems()->isEmpty()) {
            return $this->json(['error' => 'cart_empty'], 400);
        }

        $payment = $this->findNonFailedPayment($cart);
        if (null === $payment) {
            return $this->json(['error' => 'no_payment'], 400);
        }

        $paymentMethod = $payment->getMethod();
        if (!$paymentMethod instanceof PaymentMethod) {
            return $this->json(['error' => 'invalid_payment_method'], 400);
        }

        /** @var PaypalGateway $gateway */
        $gateway = $this->paymentProcessor->resolveGateway($paymentMethod);
        $orderDetails = $gateway->createOrder($payment->getAmount() ?? 0, $payment->getCurrencyCode() ?? 'EUR');

        $payment->setDetails(array_merge($payment->getDetails(), $orderDetails));
        $this->em->flush();

        return $this->json(['id' => $orderDetails['paypal_order_id']]);
    }

    /**
     * Capture une commande PayPal approuvée. Appelé par paypal_payment_controller.js (onApprove callback).
     */
    #[Route('/commande/paypal/capturer', name: 'front_checkout_paypal_capture_order', methods: ['POST'])]
    public function paypalCaptureOrder(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $data = json_decode($request->getContent(), true) ?? [];
        $orderId = (string) ($data['orderId'] ?? '');

        $cart = $this->cartContext->getCart($request);

        $payment = null;
        foreach ($cart->getPayments() as $p) {
            if (!$p instanceof Payment) {
                continue;
            }
            if (($p->getDetails()['paypal_order_id'] ?? '') === $orderId) {
                $payment = $p;

                break;
            }
        }

        if (null === $payment) {
            return $this->json(['error' => 'payment_not_found'], 404);
        }

        $paymentMethod = $payment->getMethod();
        if (!$paymentMethod instanceof PaymentMethod) {
            return $this->json(['error' => 'invalid_payment_method'], 400);
        }

        /** @var PaypalGateway $gateway */
        $gateway = $this->paymentProcessor->resolveGateway($paymentMethod);
        $captureDetails = $gateway->captureOrder($orderId);

        if ('COMPLETED' !== ($captureDetails['paypal_capture_status'] ?? '')) {
            $this->paymentProcessor->fail($payment);

            return $this->json(['error' => 'capture_failed'], 400);
        }

        $payment->setDetails(array_merge($payment->getDetails(), $captureDetails));
        $this->paymentProcessor->complete($payment);
        $request->getSession()->remove('_cart_token');

        return $this->json([
            'success' => true,
            'redirectUrl' => $this->generateUrl('front_checkout_success', ['number' => $cart->getNumber()]),
        ]);
    }

    #[Route('/commande/merci/{number}', name: 'front_checkout_success', methods: ['GET'])]
    public function success(string $number): Response
    {
        /** @var Order|null $order */
        $order = $this->em->getRepository(Order::class)->findOneBy(['number' => $number]);

        if (null === $order) {
            throw $this->createNotFoundException();
        }

        return $this->render('front/checkout/success.html.twig', [
            'order' => $order,
        ]);
    }

    private function findNonFailedPayment(Order $cart): ?Payment
    {
        foreach ($cart->getPayments() as $p) {
            if ($p instanceof Payment && Payment::STATE_FAILED !== $p->getState()) {
                return $p;
            }
        }

        return null;
    }

    private function cloneAddress(AddressInterface $src): Address
    {
        $billing = new Address();
        $billing->setFirstName($src->getFirstName());
        $billing->setLastName($src->getLastName());
        $billing->setCompany($src->getCompany());
        $billing->setStreet($src->getStreet());
        $billing->setCity($src->getCity());
        $billing->setPostcode($src->getPostcode());
        $billing->setCountryCode($src->getCountryCode());
        $billing->setPhoneNumber($src->getPhoneNumber());

        return $billing;
    }
}
