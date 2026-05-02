<?php

declare(strict_types=1);

namespace App\Tests\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BandControllerTest extends WebTestCase
{
    public function testBandIndexReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/artistes');

        self::assertResponseIsSuccessful();
    }
}
