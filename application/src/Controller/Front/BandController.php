<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Repository\AlbumRepository;
use App\Repository\BandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BandController extends AbstractController
{
    #[Route('/artistes', name: 'front_band_index')]
    public function index(BandRepository $bandRepository): Response
    {
        return $this->render('front/band/index.html.twig', [
            'bands' => $bandRepository->findAllOnline(),
        ]);
    }

    #[Route('/artiste/{slug}', name: 'front_band_show')]
    public function show(string $slug, Request $request, BandRepository $bandRepository, AlbumRepository $albumRepository, EntityManagerInterface $em): Response
    {
        $band = $bandRepository->findOneBySlug($slug);

        if (!$band || 'online' !== $band->getStatus()) {
            throw $this->createNotFoundException();
        }

        $band->setTranslatableLocale($request->getLocale());
        $em->refresh($band);

        return $this->render('front/band/show.html.twig', [
            'band' => $band,
            'albums' => $albumRepository->findLatestOnline(20, $band),
        ]);
    }
}
