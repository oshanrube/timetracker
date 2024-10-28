<?php

namespace App\Tests;

use App\Entity\Auth\Login;
use App\Factory\LoginFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CompanySelectionControllerTest extends WebTestCase
{
    use ResetDatabase, Factories;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function createAuthorizedClient()
    {
        $user = LoginFactory::createOne(
            [
                'roles' => ['ROLE_USER'],
            ]
        );
        $securityUser = $user->_real();
        $firewallContext = 'main';
        $token = new TestBrowserToken($securityUser->getRoles(), $securityUser, $firewallContext);

        $container = $this->client->getContainer();
        $container->get('security.untracked_token_storage')->setToken($token);

        if ($container->has('session.factory')) {
            $session = $container->get('session.factory')->createSession();
        } elseif ($container->has('session')) {
            $session = $container->get('session');
        } else {
            return;
        }
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId(), null, null, 'localhost');
        $this->client->getCookieJar()->set($cookie);
    }

    public function testLogin(): void
    {
        // Create company
        $this->createAuthorizedClient();
        $this->client->request('GET', '/company/selection');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Create new company', [
            'create_company[name]'      => 'Test company',
            'create_company[subdomain]' => 'test-company',
        ]);

        // ensure the company is created
        self::assertSelectorTextContains('body > div.flex.items-center.min-h-screen.p-6.bg-gray-50.dark\:bg-gray-900 > div > div > div.flex.items-center.justify-center.p-6.sm\:p-12.md\:w-1\/2 > div > ul > li:nth-child(1) > div > div > a', 'Test company');

        $this->client->clickLink("Test company");
        self::assertResponseRedirects('/dashboard');
        $this->client->followRedirect();

        self::assertSelectorNotExists('.alert-danger');
        self::assertResponseIsSuccessful();
    }
}
