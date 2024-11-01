<?php

namespace App\Tests\IntegrationTests\Services;

use App\Entity\Auth\CompanyHasSubdomain;
use App\Entity\Auth\Login;
use App\Entity\Company\Company;
use App\Factory\CompanyFactory;
use App\Factory\CompanyHasSubdomainFactory;
use App\Factory\LoginFactory;
use App\Repository\Auth\CompanyHasSubdomainRepository;
use App\Services\CompanyCreation;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompanyCreationTest extends KernelTestCase
{
    private CompanyCreation $companyCreationService;

    public function setUp(): void
    {
        self::bootKernel();
        $doctrine = self::getContainer()->get(ManagerRegistry::class);
        assert($doctrine instanceof ManagerRegistry);
        $this->companyCreationService = new CompanyCreation($doctrine);
    }

    public function testCheckSubdomainAvailability(): void
    {
        CompanyHasSubdomainFactory::createOne(['subdomain' => 'used-subdomain']);
        $result = $this->companyCreationService->checkSubdomainAvailability('unused-subdomain');
        $this->assertTrue($result);

        $result = $this->companyCreationService->checkSubdomainAvailability('used-subdomain');
        $this->assertFalse($result);
    }

    public function testCreateCompanyWithoutLoggedInUser(): void
    {
        // user not logged in
        $company = new Company();
        $user    = NULL;
        $result  = $this->companyCreationService->createCompany($company, $user);
        $this->assertFalse($result);
    }

    public function testCreateCompanyWithUsedSubdomain(): void
    {
        // used subdomain
        $company = new Company();
        $company->setName('Company 1')->setSubdomain('used-subdomain');
        $user     = new Login();
        $user
            ->setEmail("user1@example.com")
            ->setPassword('password')
            ->setRoles(['ROLE_USER']);

        $result = $this->companyCreationService->createCompany($company, $user);
        $this->assertFalse($result);
    }

    public function testCreateCompanyWithEmptySubDomain(): void
    {
        // empty subdomain
        $company = new Company();
        $company->setName('Company 2');
        $user     = new Login();
        $user
            ->setEmail("user2@example.com")
            ->setPassword('password')
            ->setRoles(['ROLE_USER']);

        $result = $this->companyCreationService->createCompany($company, $user);
        $this->assertTrue($result);
    }
}