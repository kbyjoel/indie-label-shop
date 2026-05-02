<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\ProductVariant;
use App\Repository\ProductRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/boutique', name: 'front_product_index')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $pagerfanta = new Pagerfanta(new QueryAdapter($productRepository->createEnabledPaginatedQuery()));
        $pagerfanta->setMaxPerPage(12);
        $pagerfanta->setCurrentPage(max(1, $request->query->getInt('page', 1)));

        return $this->render('front/product/index.html.twig', [
            'products' => $pagerfanta,
        ]);
    }

    #[Route('/produit/{slug}', name: 'front_product_show')]
    public function show(string $slug, Request $request, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneBySlug($slug, $request->getLocale());

        if (null === $product || !$product->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $product->setCurrentLocale($request->getLocale());
        $product->setFallbackLocale('fr');

        $variantsData = [];
        foreach ($product->getVariants() as $variant) {
            if (!$variant instanceof ProductVariant) {
                continue;
            }
            $optionValueIds = [];
            foreach ($variant->getOptionValues() as $optionValue) {
                $optionValueIds[] = (int) $optionValue->getId();
            }
            $stock = $variant->isTracked()
                ? max(0, $variant->getOnHand() - $variant->getOnHold())
                : 999;
            $variantsData[] = [
                'id' => (int) $variant->getId(),
                'price' => $variant->getPrice(),
                'stock' => $stock,
                'optionValueIds' => $optionValueIds,
            ];
        }

        return $this->render('front/product/show.html.twig', [
            'product' => $product,
            'variantsJson' => json_encode($variantsData, \JSON_THROW_ON_ERROR),
        ]);
    }
}
