<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Country;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Aropixel\AdminBundle\Component\Select2\Select2;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/%admin_path%/country', name: 'admin_country_')]
class CountryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(Country::class)
            ->setColumns([
                ['label' => 'Nom', 'orderBy' => ''],
                ['label' => 'Activé', 'orderBy' => 'enabled'],
            ])
            ->searchIn(['id', 'code'])
            ->renderJson(fn(Country $country) => [
                $country->getName(),
                sprintf('<div class="form-check form-switch"><input class="form-check-input toggle-country" type="checkbox" data-url="%s" %s></div>',
                    $this->generateUrl('admin_country_toggle', ['id' => $country->getId()]),
                    $country->isEnabled() ? 'checked' : ''
                ),
            ])
            ->render('admin/country/index.html.twig');
    }


    #[Route('/{id}/toggle', name: 'toggle', methods: ['POST'])]
    public function toggle(Country $country): Response
    {
        $country->setEnabled(!$country->isEnabled());
        $this->em->flush();

        return $this->json([
            'success' => true,
            'enabled' => $country->isEnabled(),
            'label' => $country->isEnabled() ? 'Oui' : 'Non',
        ]);
    }


    #[Route("/select2", name: "select2", methods: ["GET"])]
    public function select2(Select2 $select2): Response
    {
        return $select2
            ->withEntity(Country::class)
            ->searchIn(['name', 'code'])
            ->render(fn(Country $country) => [
                'id' => $country->getCode(),
                'text' => $country->getName(),
            ]);
    }
}
