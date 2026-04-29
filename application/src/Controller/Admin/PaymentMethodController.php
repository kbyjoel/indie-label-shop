<?php

namespace App\Controller\Admin;

use App\Entity\PaymentMethod;
use App\Form\Admin\PaymentMethodType;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%admin_path%/payment-method', name: 'admin_payment_method_')]
class PaymentMethodController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(PaymentMethod::class)
            ->setColumns([
                ['label' => 'Nom', 'orderBy' => 'name'],
                ['label' => 'Gateway', 'orderBy' => 'gatewayType', 'style' => 'width:150px;'],
                ['label' => 'Activé', 'orderBy' => 'enabled', 'style' => 'width:80px;'],
                ['label' => '', 'orderBy' => '', 'class' => 'text-end no-sort'],
            ])
            ->searchIn(['name', 'code'])
            ->setOrderColumn(0)
            ->setOrderDirection('asc')
            ->renderJson(fn (PaymentMethod $paymentMethod) => [
                $this->renderView('admin/payment_method/_link.html.twig', ['item' => $paymentMethod]),
                match ($paymentMethod->getGatewayType()) {
                    'stripe' => 'Stripe',
                    'paypal' => 'PayPal',
                    default => '—',
                },
                $paymentMethod->isEnabled() ? 'Oui' : 'Non',
                $this->renderView('admin/payment_method/_actions.html.twig', ['item' => $paymentMethod]),
            ])
            ->render('admin/payment_method/index.html.twig')
        ;
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $paymentMethod = new PaymentMethod();
        $form = $this->createForm(PaymentMethodType::class, $paymentMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paymentMethod->setCredentials($this->buildCredentials($form));
            $this->em->persist($paymentMethod);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_payment_method_edit', ['id' => $paymentMethod->getId()]);
        }

        return $this->render('admin/payment_method/form.html.twig', [
            'paymentMethod' => $paymentMethod,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PaymentMethod $paymentMethod): Response
    {
        $form = $this->createForm(PaymentMethodType::class, $paymentMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paymentMethod->setCredentials($this->buildCredentials($form));
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_payment_method_edit', ['id' => $paymentMethod->getId()]);
        }

        return $this->render('admin/payment_method/form.html.twig', [
            'paymentMethod' => $paymentMethod,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, PaymentMethod $paymentMethod): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $paymentMethod->getId(), \is_string($token) ? $token : null)) {
            $this->em->remove($paymentMethod);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_payment_method_index');
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     *
     * @return array<string, mixed>|null
     */
    private function buildCredentials(\Symfony\Component\Form\FormInterface $form): ?array
    {
        return match ($form->get('gatewayType')->getData()) {
            'stripe' => [
                'publishable_key' => $form->get('stripePublishableKey')->getData(),
                'secret_key' => $form->get('stripeSecretKey')->getData(),
            ],
            'paypal' => [
                'client_id' => $form->get('paypalClientId')->getData(),
                'secret' => $form->get('paypalSecret')->getData(),
                'mode' => $form->get('paypalMode')->getData() ?? 'sandbox',
            ],
            default => null,
        };
    }
}
