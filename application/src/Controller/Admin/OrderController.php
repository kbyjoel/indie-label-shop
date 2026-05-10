<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\Shipment;
use App\Repository\OrderRepository;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/%admin_path%/orders', name: 'admin_order_')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(Order::class)
            ->setColumns([
                ['label' => '#',          'orderBy' => 'number',        'style' => 'width:110px;'],
                ['label' => 'Date',       'orderBy' => 'createdAt',     'style' => 'width:110px;'],
                ['label' => 'Client',     'class' => 'no-sort'],
                ['label' => 'Total',      'orderBy' => 'total',         'style' => 'width:100px;'],
                ['label' => 'État',       'orderBy' => 'state',         'style' => 'width:120px;'],
                ['label' => 'Paiement',   'orderBy' => 'paymentState',  'style' => 'width:130px;'],
                ['label' => 'Expédition', 'orderBy' => 'shippingState', 'style' => 'width:130px;'],
                ['label' => '',           'class' => 'text-end no-sort'],
            ])
            ->searchIn(['number'])
            ->setOrderColumn(1)
            ->setOrderDirection('desc')
            ->renderJson(fn (Order $order) => [
                $this->renderView('admin/order/_link.html.twig', ['item' => $order]),
                $order->getCreatedAt()?->format('d/m/Y') ?? '—',
                $order->getCustomer()?->getEmail() ?? '—',
                number_format($order->getTotal() / 100, 2, ',', ' ') . ' €',
                $this->renderView('admin/order/_badge_order_state.html.twig',    ['state' => $order->getState()]),
                $this->renderView('admin/order/_badge_payment_state.html.twig',  ['state' => $order->getPaymentState()]),
                $this->renderView('admin/order/_badge_shipping_state.html.twig', ['state' => $order->getShippingState()]),
                $this->renderView('admin/order/_actions.html.twig', ['item' => $order]),
            ])
            ->render('admin/order/index.html.twig')
        ;
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->findOneForAdmin($id);
        if (null === $order) {
            throw $this->createNotFoundException();
        }

        $adjustmentsByType = [];
        foreach ($order->getAdjustments() as $adjustment) {
            $adjustmentsByType[$adjustment->getType()][] = $adjustment;
        }

        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
            'adjustmentsByType' => $adjustmentsByType,
        ]);
    }

    #[Route('/{id}/cancel', name: 'cancel', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function cancel(Request $request, Order $order): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('cancel' . $order->getId(), \is_string($token) ? $token : null)) {
            $this->addFlash('error', 'Token CSRF invalide.');

            return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
        }

        if ($order->getState() !== OrderInterface::STATE_NEW) {
            $this->addFlash('error', 'Cette commande ne peut pas être annulée.');

            return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
        }

        $order->setState(OrderInterface::STATE_CANCELLED);

        foreach ($order->getPayments() as $payment) {
            if ($payment->getState() === PaymentInterface::STATE_NEW) {
                $payment->setState('cancelled');
            }
        }

        $this->em->flush();
        $this->addFlash('notice', 'La commande a été annulée.');

        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}/ship', name: 'ship', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function ship(Request $request, Order $order): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('ship' . $order->getId(), \is_string($token) ? $token : null)) {
            $this->addFlash('error', 'Token CSRF invalide.');

            return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
        }

        if ($order->getPaymentState() !== OrderPaymentStates::STATE_PAID
            || $order->getShippingState() !== OrderShippingStates::STATE_READY) {
            $this->addFlash('error', 'Cette commande ne peut pas être marquée comme expédiée.');

            return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
        }

        foreach ($order->getShipments() as $shipment) {
            $shipment->setState('shipped');
        }
        $order->setShippingState(OrderShippingStates::STATE_SHIPPED);

        $this->em->flush();
        $this->addFlash('notice', 'La commande a été marquée comme expédiée.');

        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}/shipment/{shipmentId}/tracking', name: 'tracking', methods: ['POST'], requirements: ['id' => '\d+', 'shipmentId' => '\d+'])]
    public function tracking(Request $request, Order $order, int $shipmentId): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('tracking' . $shipmentId, \is_string($token) ? $token : null)) {
            $this->addFlash('error', 'Token CSRF invalide.');

            return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
        }

        $shipment = $this->em->getRepository(Shipment::class)->find($shipmentId);
        if (!$shipment instanceof Shipment || $shipment->getOrder() !== $order) {
            throw $this->createNotFoundException();
        }

        $tracking = $request->request->getString('tracking');
        $shipment->setTracking($tracking ?: null);
        $this->em->flush();

        $this->addFlash('notice', 'Le numéro de suivi a été enregistré.');

        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }
}
