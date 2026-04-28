<?php

namespace App\Controller\Admin;

use App\Entity\Province;
use Aropixel\AdminBundle\Component\Select2\Select2;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/%admin_path%/province', name: 'admin_province_')]
class ProvinceController extends AbstractController
{
    #[Route('/select2', name: 'select2', methods: ['GET'])]
    public function select2(Select2 $select2): Response
    {
        return $select2
            ->withEntity(Province::class)
            ->searchIn(['name', 'code'])
            ->render(fn (Province $province) => [
                'id' => $province->getCode(),
                'text' => $province->getName(),
            ])
        ;
    }
}
