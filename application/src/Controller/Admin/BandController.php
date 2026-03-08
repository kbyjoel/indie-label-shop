<?php

namespace App\Controller\Admin;

use App\Entity\Band;
use App\Form\Admin\BandType;
use App\Repository\BandRepository;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/band", name: "admin_band_")]
class BandController extends AbstractController
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
            ->create(Band::class)
            ->setColumns([
                ['label' => '', 'class' => 'no-sort', 'style' => 'width: 60px;'],
                ['label' => 'Nom', 'field' => 'name'],
                ['label' => 'Site Web', 'field' => 'website'],
                ['label' => '', 'field' => '', 'class' => 'no-sort'],
            ])
            ->searchIn(['name'])
            ->renderJson(fn(Band $band) => [
                $this->renderView('admin/band/_image.html.twig', ['item' => $band]),
                $this->renderView('admin/band/_link.html.twig', ['item' => $band]),
                $band->getWebsite(),
                $this->renderView('admin/band/_actions.html.twig', ['item' => $band]),
            ])
            ->render('admin/band/index.html.twig');
    }

    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $band = new Band();
        $form = $this->createForm(BandType::class, $band);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($band);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_band_edit', ['id' => $band->getId()]);
        }

        return $this->render('admin/band/form.html.twig', [
            'band' => $band,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function edit(Request $request, Band $band): Response
    {
        $form = $this->createForm(BandType::class, $band);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_band_edit', ['id' => $band->getId()]);
        }

        return $this->render('admin/band/form.html.twig', [
            'band' => $band,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "delete", methods: ["POST", "DELETE"])]
    public function delete(Request $request, Band $band): Response
    {
        if ($this->isCsrfTokenValid('delete' . $band->getId(), $request->request->get('_token'))) {
            $this->em->remove($band);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_band_index');
    }
}
