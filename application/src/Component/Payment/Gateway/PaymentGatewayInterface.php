<?php

declare(strict_types=1);

namespace App\Component\Payment\Gateway;

interface PaymentGatewayInterface
{
    /**
     * Initie le paiement côté gateway.
     * Retourne un tableau stocké dans Payment::details.
     *
     * @return array<string, mixed>
     */
    public function initiate(int $amount, string $currency): array;

    /**
     * Vérifie côté serveur que le paiement est bien confirmé.
     *
     * @param array<string, mixed> $details
     */
    public function verify(array $details): bool;
}
