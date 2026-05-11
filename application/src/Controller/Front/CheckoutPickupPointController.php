<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Cart\CartContext;
use App\Component\Shipping\Gateway\ShippingGatewayRegistry;
use App\Entity\Shipment;
use App\Entity\ShippingMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutPickupPointController extends AbstractController
{
    public function __construct(
        private readonly CartContext $cartContext,
        private readonly EntityManagerInterface $em,
        private readonly ShippingGatewayRegistry $gatewayRegistry,
    ) {
    }

    #[Route('/checkout/pickup-point', name: 'front_checkout_pickup_point', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
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

        /** @var Shipment $shipment */
        $shipment = $cart->getShipments()->first();
        $method = $shipment->getMethod();

        if (!$method instanceof ShippingMethod || null === $method->getGatewayCode()) {
            return $this->redirectToRoute('front_checkout_shipment');
        }

        $gateway = $this->gatewayRegistry->get($method->getGatewayCode());

        if ($request->isMethod('POST')) {
            $externalId = $request->request->getString('pickup_point_id');

            if ('' === $externalId) {
                $this->addFlash('error', 'Veuillez sélectionner un point relais.');

                return $this->redirectToRoute('front_checkout_pickup_point');
            }

            try {
                $point = $gateway->getPickupPoint($externalId, $method->getGatewayConfig());
            } catch (\InvalidArgumentException) {
                $this->addFlash('error', 'Le point relais sélectionné est invalide. Veuillez en choisir un autre.');

                return $this->redirectToRoute('front_checkout_pickup_point');
            }

            $shipment->setPickupPointExternalId($point->externalId);
            $shipment->setPickupPointName($point->name);
            $shipment->setPickupPointAddress($point->address);
            $shipment->setPickupPointPostalCode($point->postalCode);
            $shipment->setPickupPointCity($point->city);
            $shipment->setPickupPointCountryCode($point->countryCode);

            $this->em->flush();

            return $this->redirectToRoute('front_checkout_payment');
        }

        $widgetConfig = $gateway->getWidgetConfig($method->getGatewayConfig(), $cart);

        return $this->render('front/checkout/pickup_point.html.twig', [
            'cart' => $cart,
            'widgetControllerName' => $gateway->getWidgetControllerName(),
            'widgetConfig' => $widgetConfig,
        ]);
    }
}
