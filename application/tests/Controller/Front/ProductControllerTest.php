<?php

declare(strict_types=1);

namespace App\Tests\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function testProductIndexReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/boutique');

        self::assertResponseIsSuccessful();
    }
}
