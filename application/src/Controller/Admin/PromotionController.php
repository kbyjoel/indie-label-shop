<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Channel;
use App\Entity\Promotion;
use App\Form\Admin\PromotionType;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%admin_path%/promotion', name: 'admin_promotion_')]
class PromotionController extends AbstractController
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
            ->create(Promotion::class)
            ->setColumns([
                ['label' => 'Nom', 'orderBy' => 'name'],
                ['label' => 'Code', 'orderBy' => 'code', 'style' => 'width:160px;'],
                ['label' => 'Début', 'orderBy' => 'startsAt', 'style' => 'width:110px;'],
                ['label' => 'Fin', 'orderBy' => 'endsAt', 'style' => 'width:110px;'],
                ['label' => 'Coupon', 'orderBy' => 'couponBased', 'style' => 'width:90px;'],
                ['label' => '', 'orderBy' => '', 'class' => 'text-end no-sort'],
            ])
            ->searchIn(['name', 'code'])
            ->setOrderColumn(1)
            ->setOrderDirection('asc')
            ->renderJson(fn (Promotion $promotion) => [
                $this->renderView('admin/promotion/_link.html.twig', ['item' => $promotion]),
                $promotion->getCode(),
                $promotion->getStartsAt()?->format('d/m/Y') ?? '—',
                $promotion->getEndsAt()?->format('d/m/Y') ?? '—',
                $promotion->isCouponBased() ? 'Oui' : 'Non',
                $this->renderView('admin/promotion/_actions.html.twig', ['item' => $promotion]),
            ])
            ->render('admin/promotion/index.html.twig')
        ;
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $promotion = new Promotion();

        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $channel = $this->em->getRepository(Channel::class)->findOneBy(['code' => 'WEB']);
            if ($channel !== null) {
                $promotion->addChannel($channel);
            }
            $this->applyRulesConfiguration($form, $promotion);
            $this->applyActionsConfiguration($form, $promotion);
            $this->em->persist($promotion);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_promotion_edit', ['id' => $promotion->getId()]);
        }

        return $this->render('admin/promotion/form.html.twig', [
            'promotion' => $promotion,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Promotion $promotion): Response
    {
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyRulesConfiguration($form, $promotion);
            $this->applyActionsConfiguration($form, $promotion);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_promotion_edit', ['id' => $promotion->getId()]);
        }

        return $this->render('admin/promotion/form.html.twig', [
            'promotion' => $promotion,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Promotion $promotion): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $promotion->getId(), \is_string($token) ? $token : null)) {
            $this->em->remove($promotion);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_promotion_index');
    }

    /** @param FormInterface<mixed> $form */
    private function applyRulesConfiguration(FormInterface $form, Promotion $promotion): void
    {
        foreach ($form->get('rules') as $ruleForm) {
            $rule = $ruleForm->getData();
            $configuration = match ($rule->getType()) {
                'cart_quantity' => ['count' => (int) $ruleForm->get('count')->getData()],
                'nth_order' => ['nth_order' => (int) $ruleForm->get('count')->getData()],
                'item_total' => ['WEB' => ['amount' => (int) $ruleForm->get('amount')->getData()]],
                'contains_product' => ['products' => array_map(fn ($p) => $p->getId(), $ruleForm->get('products')->getData()->toArray())],
                'customer_group' => ['group_code' => $ruleForm->get('customerGroupCode')->getData()],
                'total_of_items_from_taxon' => ['taxon' => $ruleForm->get('taxonCode')->getData(), 'WEB' => ['amount' => (int) $ruleForm->get('taxonAmount')->getData()]],
                default => [],
            };
            $rule->setConfiguration($configuration);
        }
    }

    /** @param FormInterface<mixed> $form */
    private function applyActionsConfiguration(FormInterface $form, Promotion $promotion): void
    {
        foreach ($form->get('actions') as $actionForm) {
            $action = $actionForm->getData();
            $configuration = str_contains((string) $action->getType(), 'fixed')
                ? ['WEB' => ['amount' => (int) $actionForm->get('amount')->getData()]]
                : ['percentage' => $actionForm->get('percentage')->getData()];
            $action->setConfiguration($configuration);
        }
    }
}
