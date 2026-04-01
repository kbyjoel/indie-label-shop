<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/customer", name: "admin_customer_")]
class CustomerController extends AbstractController
{
    #[Route("/", name: "index", methods: ["GET", "POST"])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(Customer::class)
            ->setColumns([
                ['label' => 'Nom', 'field' => 'lastName'],
                ['label' => 'Email', 'field' => 'email'],
                ['label' => '', 'field' => '', 'class' => 'no-sort text-right'],
            ])
            ->searchIn(['firstName', 'lastName', 'email'])
            ->renderJson(fn(Customer $customer) => [
                $this->renderView('admin/customer/_link.html.twig', ['item' => $customer]),
                $customer->getEmail(),
                $this->renderView('admin/customer/_actions.html.twig', ['item' => $customer]),
            ])
            ->render('admin/customer/index.html.twig');
    }

    #[Route("/{id}/view", name: "view", methods: ["GET", "POST"])]
    public function view(): Response
    {

    }
}
