<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Channel;
use App\Form\Admin\ChannelSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%admin_path%/reglages', name: 'admin_settings_')]
class SettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $channel = $this->em->getRepository(Channel::class)->findOneBy([]);
        if (null === $channel) {
            throw $this->createNotFoundException('No channel configured.');
        }

        $form = $this->createForm(ChannelSettingsType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $baseCurrency = $channel->getBaseCurrency();
            if (null !== $baseCurrency && !$channel->getCurrencies()->contains($baseCurrency)) {
                $channel->addCurrency($baseCurrency);
            }
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));

            return $this->redirectToRoute('admin_settings_index');
        }

        return $this->render('admin/settings/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
