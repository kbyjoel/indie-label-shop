<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Repository\AlbumRepository;
use App\Repository\BandRepository;
use App\Service\PreviewUrlResolver;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    #[Route('/albums', name: 'front_album_index')]
    public function index(Request $request, AlbumRepository $albumRepository, BandRepository $bandRepository): Response
    {
        $selectedBand = null;
        $bandSlug = $request->query->getString('band');

        if ($bandSlug !== '') {
            $selectedBand = $bandRepository->findOneBySlug($bandSlug);
        }

        $pagerfanta = new Pagerfanta(new QueryAdapter($albumRepository->createOnlinePaginatedQuery($selectedBand)));
        $pagerfanta->setMaxPerPage(12);
        $pagerfanta->setCurrentPage(max(1, $request->query->getInt('page', 1)));

        return $this->render('front/album/index.html.twig', [
            'albums' => $pagerfanta,
            'bands' => $bandRepository->findAllOnline(),
            'selectedBand' => $selectedBand,
        ]);
    }

    #[Route('/album/{slug}', name: 'front_album_show')]
    public function show(string $slug, Request $request, AlbumRepository $albumRepository, EntityManagerInterface $em, PreviewUrlResolver $previewUrlResolver): Response
    {
        $album = $albumRepository->findOneBySlug($slug);

        if (!$album || $album->getStatus() !== 'online') {
            throw $this->createNotFoundException();
        }

        $album->setTranslatableLocale($request->getLocale());
        $em->refresh($album);

        $trackUrls = [];
        foreach ($album->getTracklists() as $tracklist) {
            $track = $tracklist->getTrack();
            if ($track !== null) {
                $trackUrls[$track->getId()] = [
                    'previewUrl'  => $previewUrlResolver->getPreviewUrl($track),
                    'waveformUrl' => $previewUrlResolver->getWaveformUrl($track),
                ];
            }
        }

        return $this->render('front/album/show.html.twig', [
            'album'     => $album,
            'trackUrls' => $trackUrls,
        ]);
    }
}
