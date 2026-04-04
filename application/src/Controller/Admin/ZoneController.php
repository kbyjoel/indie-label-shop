<?php

namespace App\Controller\Admin;

use App\Entity\Zone;
use App\Form\Admin\ZoneType;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/zone", name: "admin_zone_")]
class ZoneController extends AbstractController
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
            ->create(Zone::class)
            ->setColumns([
                ['label' => 'Nom', 'orderBy' => 'name'],
                ['label' => 'Code', 'orderBy' => 'code'],
                ['label' => 'Type', 'orderBy' => 'type'],
                // TODO: Add other columns here
                ['label' => '', 'orderBy' => '', 'class' => 'no-sort'],
            ])
            ->searchIn(['name', 'code']) // TODO: Add other searchable fields here
            ->renderJson(fn(Zone $zone) => [
                $this->renderView('admin/zone/_link.html.twig', ['item' => $zone]),
                $zone->getCode(),
                $zone->getType(),
                // TODO: Add other fields here
                $this->renderView('admin/zone/_actions.html.twig', ['item' => $zone]),
            ])
            ->render('admin/zone/index.html.twig');
    }

    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $zone = new Zone();
        $form = $this->createForm(ZoneType::class, $zone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($zone);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_zone_edit', ['id' => $zone->getId()]);
        }

        return $this->render('admin/zone/form.html.twig', [
            'zone' => $zone,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function edit(Request $request, Zone $zone): Response
    {
        $form = $this->createForm(ZoneType::class, $zone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_zone_edit', ['id' => $zone->getId()]);
        }

        return $this->render('admin/zone/form.html.twig', [
            'zone' => $zone,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "delete", methods: ["POST", "DELETE"])]
    public function delete(Request $request, Zone $zone): Response
    {
        if ($this->isCsrfTokenValid('delete' . $zone->getId(), $request->request->get('_token'))) {
            $this->em->remove($zone);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_zone_index');
    }
}
