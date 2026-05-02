<?php

declare(strict_types=1);

namespace App\Tests\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CartControllerTest extends WebTestCase
{
    public function testCartIndexReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/panier');

        self::assertResponseIsSuccessful();
    }

    public function testAddWithInvalidVariantReturns400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/fr/panier/ajouter', [], [], ['CONTENT_TYPE' => 'application/json'], '{"variantId": 0}');

        self::assertResponseStatusCodeSame(400);
    }
}
