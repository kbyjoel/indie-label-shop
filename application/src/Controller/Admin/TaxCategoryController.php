<?php

namespace App\Controller\Admin;

use App\Entity\TaxCategory;
use App\Form\Admin\TaxCategoryType;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%admin_path%/tax-category', name: 'admin_tax_category_')]
class TaxCategoryController extends AbstractController
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
            ->create(TaxCategory::class)
            ->setColumns([
                ['label' => 'Nom', 'orderBy' => 'name'],
                ['label' => 'Code', 'orderBy' => 'code'],
                ['label' => 'Album', 'orderBy' => '', 'class' => 'no-sort'],
                ['label' => 'Titre', 'orderBy' => '', 'class' => 'no-sort'],
                ['label' => 'Merch', 'orderBy' => '', 'class' => 'no-sort'],
                ['label' => '', 'orderBy' => '', 'class' => 'no-sort'],
            ])
            ->searchIn(['name', 'code'])
            ->setOrderColumn(0)
            ->setOrderDirection('asc')
            ->renderJson(fn (TaxCategory $taxCategory) => [
                $this->renderView('admin/tax_category/_link.html.twig', ['item' => $taxCategory]),
                $taxCategory->getCode(),
                $taxCategory->isDefaultForAlbum() ? 'Oui' : 'Non',
                $taxCategory->isDefaultForTrack() ? 'Oui' : 'Non',
                $taxCategory->isDefaultForMerch() ? 'Oui' : 'Non',
                $this->renderView('admin/tax_category/_actions.html.twig', ['item' => $taxCategory]),
            ])
            ->render('admin/tax_category/index.html.twig')
        ;
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $taxCategory = new TaxCategory();
        $form = $this->createForm(TaxCategoryType::class, $taxCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($taxCategory);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_tax_category_edit', ['id' => $taxCategory->getId()]);
        }

        return $this->render('admin/tax_category/form.html.twig', [
            'taxCategory' => $taxCategory,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TaxCategory $taxCategory): Response
    {
        $form = $this->createForm(TaxCategoryType::class, $taxCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_tax_category_edit', ['id' => $taxCategory->getId()]);
        }

        return $this->render('admin/tax_category/form.html.twig', [
            'taxCategory' => $taxCategory,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, TaxCategory $taxCategory): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $taxCategory->getId(), \is_string($token) ? $token : null)) {
            $this->em->remove($taxCategory);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_tax_category_index');
    }
}
