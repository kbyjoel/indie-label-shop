<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\TaxCategory;
use App\Entity\Track;
use App\Entity\Tracklist;
use App\Entity\TrackMasterFile;
use App\Form\Admin\AlbumType;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Aropixel\AdminBundle\Component\Select2\Select2;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%admin_path%/album', name: 'admin_album_')]
class AlbumController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly FilesystemOperator $privateStorage,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(Album::class)
            ->setColumns([
                ['label' => '', 'field' => '', 'class' => 'no-sort', 'style' => 'width: 60px;'],
                ['label' => 'Titre', 'field' => 'name'],
                ['label' => 'Numéro catalogue', 'field' => 'catalogNumber'],
                ['label' => 'Date de sortie', 'field' => 'releaseDate'],
                ['label' => '', 'field' => '', 'class' => 'text-end no-sort'],
            ])
            ->searchIn(['id', 'title', 'catalogNumber'])
            ->renderJson(fn (Album $album) => [
                $this->renderView('admin/album/_artwork.html.twig', ['item' => $album]),
                $this->renderView('admin/album/_link.html.twig', ['item' => $album]),
                $album->getCatalogNumber(),
                $album->getReleaseDate()?->format('d/m/Y'),
                $this->renderView('admin/album/_actions.html.twig', ['item' => $album]),
            ])
            ->render('admin/album/index.html.twig')
        ;
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $album = new Album();

        // Assigne la taxe par défaut pour les albums
        $defaultTaxCategory = $this->em->getRepository(TaxCategory::class)->findOneBy(['defaultForAlbum' => true]);
        if ($defaultTaxCategory) {
            $album->setTaxCategory($defaultTaxCategory);
        }

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

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
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

    #[Route('/{id}', name: 'delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Album $album): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $album->getId(), \is_string($token) ? $token : null)) {
            $this->em->remove($album);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_album_index');
    }

    #[Route('/{id}/masters', name: 'batch_masters', methods: ['GET', 'POST'])]
    public function batchMasters(Request $request, Album $album): Response
    {
        if ($request->isMethod('POST')) {
            $token = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('batch_masters_' . $album->getId(), \is_string($token) ? $token : null)) {
                $this->addFlash('error', 'Token CSRF invalide.');

                return $this->redirectToRoute('admin_album_batch_masters', ['id' => $album->getId()]);
            }

            /** @var UploadedFile[] $uploadedFiles */
            $uploadedFiles = array_values(array_filter(
                (array) ($request->files->all()['masters'] ?? []),
                fn (mixed $f): bool => $f instanceof UploadedFile,
            ));

            if (empty($uploadedFiles)) {
                $this->addFlash('error', 'Aucun fichier sélectionné.');

                return $this->redirectToRoute('admin_album_batch_masters', ['id' => $album->getId()]);
            }

            usort($uploadedFiles, fn (UploadedFile $a, UploadedFile $b): int =>
                strnatcasecmp($a->getClientOriginalName(), $b->getClientOriginalName())
            );

            /** @var Tracklist[] $tracklists */
            $tracklists = $album->getTracklists()->toArray();
            usort($tracklists, fn (Tracklist $a, Tracklist $b): int => $a->getPosition() <=> $b->getPosition());

            $maxPosition = \count($tracklists) > 0
                ? max(array_map(fn (Tracklist $tl): int => $tl->getPosition(), $tracklists))
                : 0;

            $defaultTaxCategory = $this->em->getRepository(TaxCategory::class)->findOneBy(['defaultForTrack' => true]);
            $newTrackCount = 0;
            $processedCount = 0;

            foreach ($uploadedFiles as $i => $uploadedFile) {
                $mime = $uploadedFile->getMimeType();
                if (!\in_array($mime, ['audio/flac', 'audio/wav', 'audio/x-wav', 'audio/wave'], true)) {
                    continue;
                }

                $ext = strtolower($uploadedFile->getClientOriginalExtension() ?: $uploadedFile->guessExtension() ?: 'flac');
                $originalName = $uploadedFile->getClientOriginalName();

                if (isset($tracklists[$i])) {
                    $track = $tracklists[$i]->getTrack();
                    if (null === $track) {
                        continue;
                    }
                } else {
                    $track = new Track();
                    $track->setName($this->parseTitleFromFilename($originalName));
                    if ($defaultTaxCategory) {
                        $track->setTaxCategory($defaultTaxCategory);
                    }

                    $tracklist = new Tracklist();
                    $tracklist->setPosition($maxPosition + $newTrackCount + 1);
                    $tracklist->setAlbum($album);
                    $tracklist->setTrack($track);
                    $this->em->persist($tracklist);
                    ++$newTrackCount;
                }

                $uniqueName = uniqid('master_', true) . '.' . $ext;
                $handle = fopen($uploadedFile->getPathname(), 'r');
                if (false === $handle) {
                    continue;
                }
                $this->privateStorage->writeStream('files/' . $uniqueName, $handle);
                fclose($handle);

                $fileEntity = new \Aropixel\AdminBundle\Entity\File();
                $fileEntity->setTitle(pathinfo($originalName, \PATHINFO_FILENAME));
                $fileEntity->setFilename($uniqueName);
                $fileEntity->setExtension($ext);
                $fileEntity->setPublic(false);
                $fileEntity->setCategory(TrackMasterFile::class);
                $this->em->persist($fileEntity);

                $masterFile = $track->getMasterFile();
                if (null === $masterFile) {
                    $masterFile = new TrackMasterFile();
                    $masterFile->setTrack($track);
                    $track->setMasterFile($masterFile);
                }
                $masterFile->setFile($fileEntity);
                $masterFile->setTitle(pathinfo($originalName, \PATHINFO_FILENAME));

                ++$processedCount;
            }

            $this->em->flush();
            $this->addFlash('notice', \sprintf('%d master(s) uploadé(s) avec succès.', $processedCount));

            return $this->redirectToRoute('admin_album_edit', ['id' => $album->getId()]);
        }

        /** @var Tracklist[] $tracklists */
        $tracklists = $album->getTracklists()->toArray();
        usort($tracklists, fn (Tracklist $a, Tracklist $b): int => $a->getPosition() <=> $b->getPosition());

        $tracksData = array_values(array_map(fn (Tracklist $tl): array => [
            'position' => $tl->getPosition(),
            'title' => $tl->getTrack()?->getName() ?? '',
            'hasMaster' => null !== $tl->getTrack()?->getMasterFile()?->getFile(),
        ], $tracklists));

        return $this->render('admin/album/batch_masters.html.twig', [
            'album' => $album,
            'tracksData' => $tracksData,
        ]);
    }

    private function parseTitleFromFilename(string $filename): string
    {
        $name = pathinfo($filename, \PATHINFO_FILENAME);

        return trim((string) preg_replace('/^\d+[\s\-_.]+/', '', $name));
    }

    #[Route('/select2', name: 'select2', methods: ['GET'])]
    public function select2(Select2 $select2): Response
    {
        return $select2
            ->withEntity(Album::class)
            ->searchIn(['name'])
            ->render(fn (Album $c) => [
                $c->getId(),
                $c->getName(),
            ])
        ;
    }
}
