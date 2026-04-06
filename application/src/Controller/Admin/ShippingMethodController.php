<?php

namespace App\Controller\Admin;

use App\Entity\Channel;
use App\Entity\ShippingMethod;
use App\Form\Admin\ShippingMethodType;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ShippingMethodRule;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/%admin_path%/shipping-method", name: "admin_shipping_method_")]
class ShippingMethodController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route("/", name: "index", methods: ["GET"])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(ShippingMethod::class)
            ->join('translations', 't')
            ->setColumns([
                ['label' => 'Nom', 'orderBy' => 't.name'],
                ['label' => 'Zone', 'orderBy' => 'zone', 'style' => 'width:150px;'],
                ['label' => 'Calculateur', 'orderBy' => 'calculator', 'style' => 'width:130px;'],
                ['label' => 'Poids', 'orderBy' => '', 'class' => 'no-sort', 'style' => 'width:140px;'],
                ['label' => 'Activé', 'orderBy' => 'enabled', 'style' => 'width:80px;'],
                ['label' => '', 'orderBy' => '', 'class' => 'no-sort'],
            ])
            ->searchIn(['name', 'code'])
            ->setOrderColumn(1)
            ->setOrderDirection('asc')
            ->renderJson(fn(ShippingMethod $shippingMethod) => [
                $this->renderView('admin/shipping_method/_link.html.twig', ['item' => $shippingMethod]),
                $shippingMethod->getZone()?->getName() ?? '—',
                match ($shippingMethod->getCalculator()) {
                    'flat_rate'     => 'Tarif fixe',
                    'per_unit_rate' => 'Par unité',
                    default         => '—',
                },
                $this->formatWeightRange($shippingMethod),
                $shippingMethod->isEnabled() ? 'Oui' : 'Non',
                $this->renderView('admin/shipping_method/_actions.html.twig', ['item' => $shippingMethod]),
            ])
            ->render('admin/shipping_method/index.html.twig');
    }

    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $shippingMethod = new ShippingMethod();
        $form = $this->createForm(ShippingMethodType::class, $shippingMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyConfiguration($form, $shippingMethod);
            $this->applyWeightRules($form, $shippingMethod);
            $this->em->persist($shippingMethod);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_shipping_method_edit', ['id' => $shippingMethod->getId()]);
        }

        return $this->render('admin/shipping_method/form.html.twig', [
            'shippingMethod' => $shippingMethod,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function edit(Request $request, ShippingMethod $shippingMethod): Response
    {
        $form = $this->createForm(ShippingMethodType::class, $shippingMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyConfiguration($form, $shippingMethod);
            $this->applyWeightRules($form, $shippingMethod);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_shipping_method_edit', ['id' => $shippingMethod->getId()]);
        }

        return $this->render('admin/shipping_method/form.html.twig', [
            'shippingMethod' => $shippingMethod,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "delete", methods: ["POST", "DELETE"])]
    public function delete(Request $request, ShippingMethod $shippingMethod): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $shippingMethod->getId(), is_string($token) ? $token : null)) {
            $this->em->remove($shippingMethod);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_shipping_method_index');
    }

    /** @param FormInterface<mixed> $form */
    private function applyConfiguration(FormInterface $form, ShippingMethod $shippingMethod): void
    {
        if ($shippingMethod->getCalculator() === 'weight_range') {
            $brackets = $form->get('brackets')->getData() ?? [];
            $normalized = [];
            foreach ($brackets as $bracket) {
                if (!isset($bracket['min'], $bracket['amount'])) {
                    continue;
                }
                $normalized[] = [
                    'min'    => (int) $bracket['min'],
                    'max'    => isset($bracket['max']) ? (int) $bracket['max'] : null,
                    'amount' => (int) $bracket['amount'],
                ];
            }
            $shippingMethod->setConfiguration(['brackets' => $normalized]);
            return;
        }

        $amount = $form->get('amount')->getData();
        if ($amount === null) {
            return;
        }

        $channels = $this->em->getRepository(Channel::class)->findAll();
        $configuration = [];
        foreach ($channels as $channel) {
            $configuration[$channel->getCode()] = ['amount' => (int) $amount];
        }
        $shippingMethod->setConfiguration($configuration);
    }

    /** @param FormInterface<mixed> $form */
    private function applyWeightRules(FormInterface $form, ShippingMethod $shippingMethod): void
    {
        $weightRuleTypes = [
            'total_weight_greater_than_or_equal',
            'total_weight_less_than_or_equal',
        ];

        $rulesToRemove = [];
        foreach ($shippingMethod->getRules() as $rule) {
            if (in_array($rule->getType(), $weightRuleTypes, true)) {
                $rulesToRemove[] = $rule;
            }
        }
        foreach ($rulesToRemove as $rule) {
            $shippingMethod->removeRule($rule);
        }

        // weight_range stores everything in configuration — no ShippingMethodRule needed
        if ($shippingMethod->getCalculator() === 'weight_range') {
            return;
        }

        $minWeight = $form->get('minWeight')->getData();
        $maxWeight = $form->get('maxWeight')->getData();

        if ($minWeight !== null) {
            $rule = new ShippingMethodRule();
            $rule->setType('total_weight_greater_than_or_equal');
            $rule->setConfiguration(['weight' => (int) $minWeight]);
            $shippingMethod->addRule($rule);
            $this->em->persist($rule);
        }

        if ($maxWeight !== null) {
            $rule = new ShippingMethodRule();
            $rule->setType('total_weight_less_than_or_equal');
            $rule->setConfiguration(['weight' => (int) $maxWeight]);
            $shippingMethod->addRule($rule);
            $this->em->persist($rule);
        }
    }

    private function formatWeightRange(ShippingMethod $shippingMethod): string
    {
        if ($shippingMethod->getCalculator() === 'weight_range') {
            $brackets = $shippingMethod->getConfiguration()['brackets'] ?? [];
            $count = count($brackets);
            return $count > 0 ? $count . ' tranche' . ($count > 1 ? 's' : '') : '—';
        }

        $min = $max = null;
        foreach ($shippingMethod->getRules() as $rule) {
            if ($rule->getType() === 'total_weight_greater_than_or_equal') {
                $min = $rule->getConfiguration()['weight'] ?? null;
            }
            if ($rule->getType() === 'total_weight_less_than_or_equal') {
                $max = $rule->getConfiguration()['weight'] ?? null;
            }
        }

        if ($min === null && $max === null) {
            return '—';
        }

        return ($min ?? '0') . 'g – ' . ($max !== null ? $max . 'g' : '∞');
    }
}
