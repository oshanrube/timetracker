<?php

namespace App\Tests;

use App\Factory\LoginFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class BaseLoggedInUser extends WebTestCase
{
    protected KernelBrowser $client;
    protected TranslatorInterface $translator;

    use ResetDatabase, Factories;
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->translator = $this->client->getContainer()->get(TranslatorInterface::class);
        // Sign in
        $this->createAuthorizedClient();
    }
    protected function createAuthorizedClient()
    {
        $user = LoginFactory::createOne(['roles' => ['ROLE_USER']]);
        $securityUser = $user->_real();
        $token = new TestBrowserToken($securityUser->getRoles(), $securityUser, 'main');

        $container = $this->client->getContainer();
        $container->get('security.untracked_token_storage')->setToken($token);

        if ($container->has('session.factory')) {
            $session = $container->get('session.factory')->createSession();
        }
        assert($session instanceof SessionInterface);
        $session->set('_security_main', serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId(), null, null, 'localhost');
        $this->client->getCookieJar()->set($cookie);
    }
}