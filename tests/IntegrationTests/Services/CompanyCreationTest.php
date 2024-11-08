<?php

namespace App\Tests\IntegrationTests\Services;

use App\Entity\Auth\CompanyHasSubdomain;
use App\Entity\Auth\Login;
use App\Entity\Company\Company;
use App\Factory\CompanyHasSubdomainFactory;
use App\Services\AppDoctrineRegistry;
use App\Services\CompanyCreation;
use App\Services\DatabaseManager\DatabaseCreator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Persistence\ResetDatabase\ResetDatabaseManager;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use PHPUnit\Framework\Attributes\Before;

class CompanyCreationTest extends KernelTestCase
{
    use ResetDatabase, Factories;
    private CompanyCreation $companyCreationService;

    public function setUp(): void
    {
        self::bootKernel();
        $doctrine = self::getContainer()->get(AppDoctrineRegistry::class);
        assert($doctrine instanceof ManagerRegistry);
        $database_creator = self::getContainer()->get(DatabaseCreator::class);
        assert($database_creator instanceof DatabaseCreator);
        $this->companyCreationService = new CompanyCreation($doctrine, $database_creator);
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
        //
        $company_has_subdomain = new CompanyHasSubdomain();
        $company_has_subdomain->setSubdomain('used-subdomain');
        $company_has_subdomain->setName('c old company');
        $doctrine = self::getContainer()->get(ManagerRegistry::class);
        assert($doctrine instanceof ManagerRegistry);
        $doctrine->getManager('auth')->persist($company_has_subdomain);
        // used subdomain
        $company = new Company();
        $company->setName('Company 1')
                ->setSubdomain('used-subdomain');
        $user = new Login();
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
        $user = new Login();
        $user
            ->setEmail("user2@example.com")
            ->setPassword('password')
            ->setRoles(['ROLE_USER']);

        $result = $this->companyCreationService->createCompany($company, $user);
        $this->assertTrue($result);

        $doctrine = self::getContainer()->get(AppDoctrineRegistry::class);
        assert($doctrine instanceof ManagerRegistry);
        $db_company = $doctrine
            ->getManager('company')
            ->getRepository(Company::class)
            ->findOneBy(['name' => 'Company 2']);
        $this->assertNotNull($db_company);

        //TODO fix this cleanup
        $database_creator = self::getContainer()->get(DatabaseCreator::class);
        assert($database_creator instanceof DatabaseCreator);
        $database_creator->loadDatabase($db_company->getId());
        $connection = self::getContainer()->get('doctrine.orm.user_company_connection');
        $database_creator->deleteDatabaseIfExists($connection);
    }
}