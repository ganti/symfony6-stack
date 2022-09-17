<?php

namespace App\Tests\Core;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class AuthTest extends WebTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

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

    public function testLoginRedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseRedirects('/en/login');
    }

    public function testUserAccessingAuthPages(): void
    {
        $pages = ['/en/login','/register','/reset-password'];
        $client = $this->clientLoginAsAdmin();
        foreach ($pages as $page)
        {
            $client->request('GET', $page);
            $this->assertResponseRedirects();
            $this->assertTrue($client->getResponse()->isRedirect('/authbridge'), $page);
        }

    }

    public function testAnonymousAccessingAuthPages(): void
    {
        $pages = ['/en/login','/register','/reset-password'];
        $client = static::createClient();
        foreach ($pages as $page)
        {
            $client->request('GET', $page);
            $assert = $client->getResponse()->isRedirect('/authbridge') == false ;
            $this->assertTrue($assert, $page);
        }
    }

    public function testLogout(): void
    {
        $client = $this->clientLoginAsAdmin();
        $client->request('GET', '/logout');
        $this->assertTrue($client->getResponse()->isRedirect());
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

    
    public function testParamRegistrationActive(): void
    {
        $client = static::createClient();   
        $client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Registration');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testParamPasswortResetActive(): void
    {   
        $client = static::createClient();
        $client->request('GET', '/reset-password');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Reset Password');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}
