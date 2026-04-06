<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\TaxCategory;
use App\Form\Admin\ProductType;
use App\Form\Admin\ProductVariantType;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Aropixel\AdminBundle\Component\Select2\Select2;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/%admin_path%/product", name: "admin_product_")]
class ProductController extends AbstractController
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
            ->create(Product::class)
            ->join('translations', 't') // Left join event.category with alias 'c'
            ->setColumns([
                ['label' => 'Nom', 'orderBy' => 't.name'],
                ['label' => '', 'orderBy' => '', 'class' => 'text-end no-sort'],
            ])
            ->filter(function(QueryBuilder $qb) {
                $qb->andWhere($qb->getRootAliases()[0] . ' NOT INSTANCE OF ' . Album::class);
            })
            ->searchIn(['name'])
            ->renderJson(fn(Product $product) => [
                $this->renderView('admin/product/_link.html.twig', ['item' => $product]),
                $this->renderView('admin/product/_actions.html.twig', ['item' => $product]),
            ])
            ->render('admin/product/index.html.twig');
    }

    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $product = new Product();

        // Assigne la taxe par défaut pour le merchandising
        $defaultTaxCategory = $this->em->getRepository(TaxCategory::class)->findOneBy(['defaultForMerch' => true]);
        if ($defaultTaxCategory) {
            $product->setTaxCategory($defaultTaxCategory);
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($product);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_product_edit', ['id' => $product->getId()]);
        }

        return $this->render('admin/product/form.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function edit(Request $request, Product $product): Response
    {
        // On récupère les IDs des options actuelles avant la soumission du formulaire
        $originalOptions = $product->getOptions()->map(fn(\Sylius\Component\Product\Model\ProductOptionInterface $option) => $option->getId())->toArray();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Identifier les nouvelles options ajoutées
            foreach ($product->getOptions() as $option) {
                if (!in_array($option->getId(), $originalOptions)) {
                    // Pour chaque nouvelle option, on ajoute ses valeurs aux variantes existantes
                    foreach ($product->getVariants() as $variant) {
                        foreach ($option->getValues() as $optionValue) {
                            if (!$variant->getOptionValues()->contains($optionValue)) {
                                $variant->addOptionValue($optionValue);
                            }
                        }
                    }
                }
            }

            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_product_edit', ['id' => $product->getId()]);
        }

        return $this->render('admin/product/form.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/variant/{id}/edit", name: "variant_edit", methods: ["GET", "POST"])]
    public function variantEdit(Request $request, ProductVariant $variant): Response
    {
        $form = $this->createForm(ProductVariantType::class, $variant, [
            'action' => $this->generateUrl('admin_product_variant_edit', ['id' => $variant->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            if ($request->isXmlHttpRequest()) {
                return new Response(null, Response::HTTP_NO_CONTENT);
            }

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_product_edit', ['id' => $variant->getProduct()?->getId()]);
        }

        $template = $request->isXmlHttpRequest() ? 'admin/product/variant/_form.html.twig' : 'admin/product/variant/edit.html.twig';
        return $this->render($template, [
            'variant' => $variant,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "delete", methods: ["POST", "DELETE"])]
    public function delete(Request $request, Product $product): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $product->getId(), is_string($token) ? $token : null)) {
            $this->em->remove($product);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_product_index');
    }

    #[Route("/select2", name: "select2", methods: ["GET"])]
    public function select2(Select2 $select2): Response
    {
        return $select2
            ->withEntity(Product::class)
            ->searchIn(['name'])
            ->render(fn(Product $p) => [
                $p->getId(),
                $p->getName(),
            ]);
    }
}
