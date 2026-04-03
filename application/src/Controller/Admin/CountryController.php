<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Country;
use App\Form\Admin\CountryType;
use App\Repository\CountryRepository;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/country', name: 'admin_country_')]
class CountryController extends AbstractController
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
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
                ['label' => '', 'orderBy' => '', 'class' => 'no-sort'],
            ])
            ->searchIn(['id', 'code'])
            ->renderJson(fn(Country $country) => [
                $country->getName(),
                $country->isEnabled() ? 'Oui' : 'Non',
                $this->renderView('admin/country/_actions.html.twig', ['item' => $country]),
            ])
            ->render('admin/country/index.html.twig');
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $country = new Country();
        $form = $this->createForm(CountryType::class, $country);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($country);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_country_edit', ['id' => $country->getId()]);
        }

        return $this->render('admin/country/form.html.twig', [
            'country' => $country,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Country $country): Response
    {
        $form = $this->createForm(CountryType::class, $country);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_country_edit', ['id' => $country->getId()]);
        }

        return $this->render('admin/country/form.html.twig', [
            'country' => $country,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Country $country): Response
    {
        if ($this->isCsrfTokenValid('delete' . $country->getId(), $request->request->get('_token'))) {
            $this->em->remove($country);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_country_index');
    }
}
