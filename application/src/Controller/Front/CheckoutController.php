<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Cart\CartContext;
use App\Component\Shipment\ShippingCalculator;
use App\Entity\Address;
use App\Entity\Adjustment;
use Sylius\Component\Core\Model\AddressInterface;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\PaymentMethod;
use App\Entity\Shipment;
use App\Entity\ShippingMethod;
use App\Entity\ShopUser;
use App\Form\Front\CheckoutAddressType;
use App\Form\Front\CheckoutShipmentType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CheckoutController extends AbstractController
{
    public function __construct(
        private CartContext $cartContext,
        private EntityManagerInterface $em,
        private ShippingCalculator $shippingCalculator,
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

            $cart->setTotal($cart->getItemsTotal() + $amount);
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

    #[Route('/commande/confirmer', name: 'front_checkout_confirm', methods: ['POST'])]
    public function confirm(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('checkout_confirm', (string) $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $cart = $this->cartContext->getCart($request);
        if ($cart->getItems()->isEmpty()) {
            return $this->redirectToRoute('front_cart_index');
        }

        $paymentMethodId = $request->request->getInt('payment_method_id');
        /** @var PaymentMethod|null $paymentMethod */
        $paymentMethod = $this->em->find(PaymentMethod::class, $paymentMethodId);
        if (null === $paymentMethod) {
            return $this->redirectToRoute('front_checkout_payment');
        }

        $payment = new Payment();
        $payment->setMethod($paymentMethod);
        $payment->setAmount($cart->getTotal());
        $payment->setCurrencyCode($cart->getCurrencyCode() ?? 'EUR');
        $cart->addPayment($payment);

        $cart->setState(Order::STATE_NEW);
        $cart->setNumber('ORD-' . strtoupper(substr(uniqid(), -8)));

        $this->em->flush();

        $request->getSession()->remove('_cart_token');

        return $this->redirectToRoute('front_checkout_success', ['number' => $cart->getNumber()]);
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
