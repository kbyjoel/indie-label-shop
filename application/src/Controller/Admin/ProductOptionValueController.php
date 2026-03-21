<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
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

#[Route("/product-option-value", name: "admin_product_option_value_")]
class ProductOptionValueController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route("/select2", name: "select2", methods: ["GET"])]
    public function select2(Request $request, Select2 $select2): Response
    {
        $optionId = $request->query->get('optionId');

        return $select2
            ->withEntity(ProductOptionValue::class)
            ->searchIn(['name'])
            ->filter(function($qb) use ($optionId) {
                if ($optionId) {
                    $qb->andWhere('e.option = :optionId')
                       ->setParameter('optionId', $optionId);
                }
            })
            ->render(fn(ProductOptionValue $pov) => [
                $pov->getId(),
                $pov->getName(),
            ]);
    }
}
