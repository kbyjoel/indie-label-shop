<?php

declare(strict_types=1);

namespace App\Tests\Component\Mail;

use App\Component\Mail\Message\SendOrderConfirmedMessage;
use App\Component\Mail\MessageHandler\SendOrderConfirmedHandler;
use App\Entity\Order;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class SendOrderConfirmedHandlerTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private MailerInterface&MockObject $mailer;
    private Environment&MockObject $twig;
    private SendOrderConfirmedHandler $handler;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->twig = $this->createMock(Environment::class);

        $this->handler = new SendOrderConfirmedHandler(
            $this->em,
            $this->mailer,
            $this->twig,
            'noreply@test.com',
        );
    }

    public function testSendsEmailAndSetsConfirmationSentAt(): void
    {
        $customer = $this->createCustomerStub('buyer@example.com');
        $order = $this->createOrderStub(42, null, $customer);

        $this->em->expects(self::once())
            ->method('find')
            ->with(Order::class, 42)
            ->willReturn($order)
        ;

        $this->twig->expects(self::once())
            ->method('render')
            ->with('emails/pdf/invoice.html.twig', ['order' => $order])
            ->willReturn('<html>invoice</html>')
        ;

        $this->mailer->expects(self::once())
            ->method('send')
        ;

        $this->em->expects(self::once())
            ->method('flush')
        ;

        ($this->handler)(new SendOrderConfirmedMessage(42));
    }

    public function testIdempotentWhenConfirmationAlreadySent(): void
    {
        $customer = $this->createCustomerStub('buyer@example.com');
        $order = $this->createOrderStub(42, new \DateTimeImmutable(), $customer);

        $this->em->expects(self::once())
            ->method('find')
            ->with(Order::class, 42)
            ->willReturn($order)
        ;

        $this->twig->expects(self::never())
            ->method('render')
        ;

        $this->mailer->expects(self::never())
            ->method('send')
        ;

        ($this->handler)(new SendOrderConfirmedMessage(42));
    }

    public function testSkipsWhenOrderNotFound(): void
    {
        $this->em->expects(self::once())
            ->method('find')
            ->willReturn(null)
        ;

        $this->twig->expects(self::never())
            ->method('render')
        ;

        $this->mailer->expects(self::never())
            ->method('send')
        ;

        ($this->handler)(new SendOrderConfirmedMessage(99));
    }

    /**
     * @return \Sylius\Component\Core\Model\CustomerInterface&Stub
     */
    private function createCustomerStub(string $email): object
    {
        $customer = $this->createStub(\Sylius\Component\Core\Model\CustomerInterface::class);
        $customer->method('getEmail')->willReturn($email);

        return $customer;
    }

    /**
     * @return Order&Stub
     */
    private function createOrderStub(int $id, ?\DateTimeImmutable $sentAt, object $customer): Order
    {
        $order = $this->createStub(Order::class);
        $order->method('getId')->willReturn($id);
        $order->method('getConfirmationEmailSentAt')->willReturn($sentAt);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getItems')->willReturn(new ArrayCollection());
        $order->method('getTotal')->willReturn(1000);
        $order->method('getNumber')->willReturn('ORD-00001');

        return $order;
    }
}
