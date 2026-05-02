<?php

declare(strict_types=1);

namespace App\Tests\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CheckoutControllerTest extends WebTestCase
{
    public function testAddressRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/commande/adresse');

        self::assertResponseRedirects();
    }

    public function testShipmentRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/commande/livraison');

        self::assertResponseRedirects();
    }

    public function testPaymentRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/commande/paiement');

        self::assertResponseRedirects();
    }
}
