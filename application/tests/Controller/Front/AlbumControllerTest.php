<?php

declare(strict_types=1);

namespace App\Tests\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumControllerTest extends WebTestCase
{
    public function testAlbumIndexReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/albums');

        self::assertResponseIsSuccessful();
    }
}
