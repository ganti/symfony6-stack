<?php

namespace App\Tests\Core;

use PHPUnit\Exception;
use App\Repository\UserRepository;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthTest extends WebTestCase
{

    private function clientLoginAsAdmin()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByUsername('admin');
        $client->loginUser($user);
        return $client;
    }

    public function testOpenAdminAsAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/en/login');
    }

    public function testOpenAdminAsAdminUser(): void
    {
        
        $client = $this->clientLoginAsAdmin();
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    public function testLogout(): void
    {
        $client = $this->clientLoginAsAdmin();
        $client->request('GET', '/logout');
        $this->assertTrue($client->getResponse()->isRedirect());
    }
   
  
    public function testLoginRedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseRedirects('/en/login');
    }

    public function testUserAccessingNonAuthPages(): void
    {
        $client = $this->clientLoginAsAdmin();
        foreach (['/en/login','/register','/reset-password','/verify/email'] as $page)
        {
            $client->request('GET', $page);
            $this->assertTrue($client->getResponse()->isRedirect(), $page);   
        }

    }

    /**
     * @testdox Error 404
     */
    public function test404Page(): void
    {
        $client = static::createClient();
        $client->request('GET', '/foobarXCFASDFGKPKéSFD');
        $this->assertResponseStatusCodeSame(404);
    }
}
