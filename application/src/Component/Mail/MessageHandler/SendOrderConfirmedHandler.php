<?php

declare(strict_types=1);

namespace App\Component\Mail\MessageHandler;

use App\Component\Mail\Message\SendOrderConfirmedMessage;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsMessageHandler]
class SendOrderConfirmedHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        private readonly string $mailerFrom,
    ) {
    }

    public function __invoke(SendOrderConfirmedMessage $message): void
    {
        $order = $this->em->find(Order::class, $message->getOrderId());
        if (!$order instanceof Order) {
            return;
        }

        if (null !== $order->getConfirmationEmailSentAt()) {
            return;
        }

        $customer = $order->getCustomer();
        if (null === $customer || null === $customer->getEmail()) {
            return;
        }

        $locale = $order->getLocaleCode() ?? 'fr';

        $pdfHtml = $this->twig->render('emails/pdf/invoice.html.twig', ['order' => $order, 'locale' => $locale]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($pdfHtml);
        $dompdf->render();
        $pdfContent = (string) $dompdf->output();

        $subject = $this->translator->trans('email.order_confirmed.subject', ['number' => $order->getNumber()], 'messages', $locale);
        $pdfFilename = $this->translator->trans('email.order_confirmed.pdf_filename', [], 'messages', $locale);

        $email = (new TemplatedEmail())
            ->from($this->mailerFrom)
            ->to((string) $customer->getEmail())
            ->subject($subject)
            ->htmlTemplate('emails/order_confirmed.html.twig')
            ->textTemplate('emails/order_confirmed.txt.twig')
            ->context(['order' => $order, 'locale' => $locale])
            ->attach($pdfContent, $pdfFilename . '-' . $order->getNumber() . '.pdf', 'application/pdf')
        ;

        $this->mailer->send($email);

        $order->setConfirmationEmailSentAt(new \DateTimeImmutable());
        $this->em->flush();
    }
}
