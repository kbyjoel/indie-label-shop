<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\Admin\MediaType;
use App\Repository\MediaRepository;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/media", name: "admin_media_")]
class MediaController extends AbstractController
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route("/", name: "index", methods: ["GET", "POST"])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(Media::class)
            ->setColumns([
                ['label' => 'Nom', 'field' => 'name'],
                ['label' => 'Numérique', 'field' => 'isDigital'],
                ['label' => '', 'field' => '', 'class' => 'no-sort text-right'],
            ])
            ->searchIn(['id', 'name'])
            ->renderJson(fn(Media $media) => [
                $this->renderView('admin/media/_link.html.twig', ['item' => $media]),
                $this->renderView('admin/media/_is_digital.html.twig', ['item' => $media]),
                $this->renderView('admin/media/_actions.html.twig', ['item' => $media]),
            ])
            ->render('admin/media/index.html.twig');
    }

    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($media);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_media_edit', ['id' => $media->getId()]);
        }

        return $this->render('admin/media/form.html.twig', [
            'media' => $media,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function edit(Request $request, Media $media): Response
    {
        $form = $this->createForm(MediaType::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_media_edit', ['id' => $media->getId()]);
        }

        return $this->render('admin/media/form.html.twig', [
            'media' => $media,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "delete", methods: ["POST", "DELETE"])]
    public function delete(Request $request, Media $media): Response
    {
        if ($this->isCsrfTokenValid('delete' . $media->getId(), $request->request->get('_token'))) {
            $this->em->remove($media);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_media_index');
    }
}
