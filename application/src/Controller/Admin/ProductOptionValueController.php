<?php

namespace App\Controller\Admin;

use App\Entity\ProductOptionValue;
use Aropixel\AdminBundle\Component\Select2\Select2;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/%admin_path%/product-option-value', name: 'admin_product_option_value_')]
class ProductOptionValueController extends AbstractController
{
    #[Route('/select2', name: 'select2', methods: ['GET'])]
    public function select2(Request $request, Select2 $select2): Response
    {
        $optionId = $request->query->get('optionId');

        return $select2
            ->withEntity(ProductOptionValue::class)
            ->searchIn(['name'])
            ->filter(function ($qb) use ($optionId) {
                if ($optionId) {
                    $qb->andWhere('e.option = :optionId')
                        ->setParameter('optionId', $optionId)
                    ;
                }
            })
            ->render(fn (ProductOptionValue $pov) => [
                $pov->getId(),
                $pov->getName(),
            ])
        ;
    }
}
