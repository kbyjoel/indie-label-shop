<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Aropixel\BlogBundle\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{
    #[Route('/news', name: 'front_post_index')]
    public function index(Request $request, PostRepository $postRepository): Response
    {
        $qb = $postRepository->qbPublished('p', ['createdAt' => 'DESC']);
        $pagerfanta = new Pagerfanta(new QueryAdapter($qb));
        $pagerfanta->setMaxPerPage(9);
        $pagerfanta->setCurrentPage(max(1, $request->query->getInt('page', 1)));

        return $this->render('front/post/index.html.twig', [
            'posts' => $pagerfanta,
        ]);
    }

    #[Route('/news/{slug}', name: 'front_post_show')]
    public function show(string $slug, Request $request, PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        $post = $postRepository->findOnePublished($slug);

        if (!$post) {
            throw $this->createNotFoundException();
        }

        $post->setTranslatableLocale($request->getLocale());
        $em->refresh($post);

        return $this->render('front/post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
