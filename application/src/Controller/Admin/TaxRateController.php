<?php

namespace App\Controller\Admin;

use App\Entity\TaxRate;
use App\Form\Admin\TaxRateType;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%admin_path%/tax-rate', name: 'admin_tax_rate_')]
class TaxRateController extends AbstractController
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
            ->create(TaxRate::class)
            ->setColumns([
                ['label' => 'Nom', 'orderBy' => 'name'],
                ['label' => 'Code', 'orderBy' => 'code'],
                ['label' => 'Catégorie', 'orderBy' => 'cat.name'],
                ['label' => 'Zone', 'orderBy' => 'z.name'],
                ['label' => 'Montant', 'orderBy' => 'amount'],
                ['label' => '', 'orderBy' => '', 'class' => 'no-sort'],
            ])
            ->join('category', 'cat')
            ->join('zone', 'z')
            ->searchIn(['name', 'code'])
            ->setOrderColumn(0)
            ->setOrderDirection('asc')
            ->renderJson(fn (TaxRate $taxRate) => [
                $this->renderView('admin/tax_rate/_link.html.twig', ['item' => $taxRate]),
                $taxRate->getCode(),
                $taxRate->getCategory()?->getName() ?? '',
                $taxRate->getZone()?->getName() ?? '',
                number_format((float) $taxRate->getAmount() * 100, 2) . ' %',
                $this->renderView('admin/tax_rate/_actions.html.twig', ['item' => $taxRate]),
            ])
            ->render('admin/tax_rate/index.html.twig')
        ;
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $taxRate = new TaxRate();
        $form = $this->createForm(TaxRateType::class, $taxRate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taxRate->setCalculator('default');
            $this->em->persist($taxRate);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_tax_rate_edit', ['id' => $taxRate->getId()]);
        }

        return $this->render('admin/tax_rate/form.html.twig', [
            'taxRate' => $taxRate,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TaxRate $taxRate): Response
    {
        $form = $this->createForm(TaxRateType::class, $taxRate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_tax_rate_edit', ['id' => $taxRate->getId()]);
        }

        return $this->render('admin/tax_rate/form.html.twig', [
            'taxRate' => $taxRate,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, TaxRate $taxRate): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $taxRate->getId(), \is_string($token) ? $token : null)) {
            $this->em->remove($taxRate);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_tax_rate_index');
    }
}
