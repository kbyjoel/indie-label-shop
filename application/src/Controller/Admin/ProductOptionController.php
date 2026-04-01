<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\ProductOption;
use App\Form\Admin\ProductOptionType;
use App\Repository\ProductOptionRepository;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Aropixel\AdminBundle\Component\Select2\Select2;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/product-option", name: "admin_product_option_")]
class ProductOptionController extends AbstractController
{
    public function __construct(
        private readonly ProductOptionRepository $productOptionRepository,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route("/", name: "index", methods: ["GET"])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(ProductOption::class)
            ->join('translations', 't') // Left join event.category with alias 'c'
            ->setColumns([
                ['label' => 'Nom', 'field' => 't.name'],
                ['label' => 'Code', 'field' => 'code'],
                ['label' => 'Position', 'field' => 'position'],
                ['label' => '', 'field' => '', 'class' => 'no-sort'],
            ])
            ->searchIn(['id', 'code'])
            ->renderJson(fn(ProductOption $productOption) => [
                $this->renderView('admin/product_option/_link.html.twig', ['item' => $productOption]),
                $productOption->getCode(),
                $productOption->getPosition(),
                $this->renderView('admin/product_option/_actions.html.twig', ['item' => $productOption]),
            ])
            ->render('admin/product_option/index.html.twig');
    }

    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $productOption = new ProductOption();
        $form = $this->createForm(ProductOptionType::class, $productOption);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($productOption);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_product_option_edit', ['id' => $productOption->getId()]);
        }

        return $this->render('admin/product_option/form.html.twig', [
            'productOption' => $productOption,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function edit(Request $request, ProductOption $productOption): Response
    {
        $form = $this->createForm(ProductOptionType::class, $productOption);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_product_option_edit', ['id' => $productOption->getId()]);
        }

        return $this->render('admin/product_option/form.html.twig', [
            'productOption' => $productOption,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "delete", methods: ["POST", "DELETE"])]
    public function delete(Request $request, ProductOption $productOption): Response
    {
        if ($this->isCsrfTokenValid('delete' . $productOption->getId(), $request->request->get('_token'))) {
            $this->em->remove($productOption);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_product_option_index');
    }

    #[Route("/select2", name: "select2", methods: ["GET"])]
    public function select2(Select2 $select2): Response
    {
        return $select2
            ->withEntity(ProductOption::class)
            ->searchIn(['name'])
            ->render(fn(ProductOption $po) => [
                $po->getId(),
                $po->getName(),
            ]);
    }
}
