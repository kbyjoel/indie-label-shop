<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\Admin\AlbumType;
use App\Repository\AlbumRepository;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Aropixel\AdminBundle\Component\Select2\Select2;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/album", name: "admin_album_")]
class AlbumController extends AbstractController
{
    public function __construct(
        private readonly AlbumRepository $albumRepository,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route("/", name: "index", methods: ["GET"])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(Album::class)
            ->setColumns([
                ['label' => '', 'field' => '', 'class' => 'no-sort', 'style' => 'width: 60px;'],
                ['label' => 'Titre', 'field' => 'name'],
                ['label' => 'Numéro catalogue', 'field' => 'catalogNumber'],
                ['label' => 'Date de sortie', 'field' => 'releaseDate'],
                ['label' => '', 'field' => '', 'class' => 'no-sort'],
            ])
            ->searchIn(['id', 'title', 'catalogNumber'])
            ->renderJson(fn(Album $album) => [
                $this->renderView('admin/album/_artwork.html.twig', ['item' => $album]),
                $this->renderView('admin/album/_link.html.twig', ['item' => $album]),
                $album->getCatalogNumber(),
                $album->getReleaseDate()?->format('d/m/Y'),
                $this->renderView('admin/album/_actions.html.twig', ['item' => $album]),
            ])
            ->render('admin/album/index.html.twig');
    }

    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($album);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_album_edit', ['id' => $album->getId()]);
        }

        return $this->render('admin/album/form.html.twig', [
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function edit(Request $request, Album $album): Response
    {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_album_edit', ['id' => $album->getId()]);
        }

        return $this->render('admin/album/form.html.twig', [
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "delete", methods: ["POST", "DELETE"])]
    public function delete(Request $request, Album $album): Response
    {
        if ($this->isCsrfTokenValid('delete' . $album->getId(), $request->request->get('_token'))) {
            $this->em->remove($album);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_album_index');
    }

    #[Route("/select2", name: "select2", methods: ["GET"])]
    public function select2(Select2 $select2): Response
    {
        return $select2
            ->withEntity(Album::class)
            ->searchIn(['name'])
            ->render(fn(Album $c) => [
                $c->getId(),
                $c->getName(),
            ]);
    }
}
