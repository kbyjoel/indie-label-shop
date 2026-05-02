<?php

declare(strict_types=1);

namespace App\Tests\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountControllerTest extends WebTestCase
{
    public function testLoginPageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/compte/connexion');

        self::assertResponseIsSuccessful();
    }

    public function testRegisterPageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/compte/inscription');

        self::assertResponseIsSuccessful();
    }

    public function testDashboardRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/compte');

        self::assertResponseRedirects();
    }
}
