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
use Twig\Environment;

#[AsMessageHandler]
class SendOrderConfirmedHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
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

        $pdfHtml = $this->twig->render('emails/pdf/invoice.html.twig', ['order' => $order]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($pdfHtml);
        $dompdf->render();
        $pdfContent = (string) $dompdf->output();

        $email = (new TemplatedEmail())
            ->from($this->mailerFrom)
            ->to((string) $customer->getEmail())
            ->subject('Votre commande #' . $order->getNumber())
            ->htmlTemplate('emails/order_confirmed.html.twig')
            ->textTemplate('emails/order_confirmed.txt.twig')
            ->context(['order' => $order])
            ->attach($pdfContent, 'facture-' . $order->getNumber() . '.pdf', 'application/pdf')
        ;

        $this->mailer->send($email);

        $order->setConfirmationEmailSentAt(new \DateTimeImmutable());
        $this->em->flush();
    }
}
