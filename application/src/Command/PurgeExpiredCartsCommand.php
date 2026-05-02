<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:cart:purge-expired',
    description: 'Delete cart orders whose updatedAt is older than the configured TTL',
)]
class PurgeExpiredCartsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        #[Autowire(param: 'app.cart.expiration_days')]
        private int $expirationDays,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $threshold = new \DateTimeImmutable(\sprintf('-%d days', $this->expirationDays));

        $deleted = $this->em->createQuery(
            'DELETE FROM App\Entity\Order o WHERE o.state = :state AND o.updatedAt < :threshold'
        )
            ->setParameter('state', Order::STATE_CART)
            ->setParameter('threshold', $threshold)
            ->execute()
        ;

        $io->success(\sprintf('Deleted %d expired cart(s) older than %d days.', $deleted, $this->expirationDays));

        return Command::SUCCESS;
    }
}
