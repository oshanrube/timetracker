<?php

namespace App\Tests\IntegrationTests\Services\DatabaseManager;

use App\Entity\Company\Company;
use App\Services\DatabaseManager\DatabaseCreator;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DatabaseCreatorTest extends KernelTestCase
{
    public function testCreateDatabaseConnection()
    {
        $this->expectNotToPerformAssertions();
        $params_bag = self::getContainer()->get(ParameterBagInterface::class);
        assert($params_bag instanceof ParameterBagInterface);
        $db_creator = new DatabaseCreator($params_bag, self::getContainer());

        $company_id = 233;
        // create connection
        $db_creator->createDatabaseConnection($company_id);
    }

    public function testDeleteDatabaseIfExistsWithoutDb()
    {
        $this->expectNotToPerformAssertions();
        $params_bag = self::getContainer()->get(ParameterBagInterface::class);
        assert($params_bag instanceof ParameterBagInterface);
        $db_creator = new DatabaseCreator($params_bag, self::getContainer());

        $company_id = 233;
        // create connection
        $connection = $db_creator->loadDatabaseConnection($company_id);
        // delete Database
        $db_creator->deleteDatabaseIfExists($connection);
    }

    public function testDeleteDatabaseIfExistsWithDb()
    {
        $this->expectNotToPerformAssertions();
        $params_bag = self::getContainer()->get(ParameterBagInterface::class);
        assert($params_bag instanceof ParameterBagInterface);
        $db_creator = new DatabaseCreator($params_bag, self::getContainer());

        $company_id = 233;
        // create connection
        $connection = $db_creator->loadDatabaseConnection($company_id);
        // delete Database if old one exists
        $db_creator->deleteDatabaseIfExists($connection);
        // create Database
        $db_creator->createDatabaseIfNotExists($connection);
        // delete Database
        $db_creator->deleteDatabaseIfExists($connection);
    }

    public function testCreateDatabaseIfNotExistsWithDb()
    {
        $this->expectNotToPerformAssertions();
        $params_bag = self::getContainer()->get(ParameterBagInterface::class);
        assert($params_bag instanceof ParameterBagInterface);
        $db_creator = new DatabaseCreator($params_bag, self::getContainer());

        $company_id = 233;
        // create connection
        $connection = $db_creator->loadDatabaseConnection($company_id);
        // create Database
        $db_creator->createDatabaseIfNotExists($connection);
    }

    /**
     * @throws Exception
     */
    public function testLoadDatabaseFromCompanyId()
    {
        $params_bag = self::getContainer()->get(ParameterBagInterface::class);
        assert($params_bag instanceof ParameterBagInterface);
        $db_creator = new DatabaseCreator($params_bag, self::getContainer());

        $company_id = 233;
        // create database tables
        $db_creator->loadDatabase($company_id);
        // try creating a new Entity
        $company = new Company();
        $company->setId($company_id);
        $company->setName("New Company name");

        $doctrine = self::getContainer()->get('doctrine');
        $doctrine->getManager('company')->persist($company);
        $doctrine->getManager('company')->flush();

        $company = $doctrine->getManager('company')->getRepository(Company::class)->find($company_id);
        $this->assertNotNull($company);
    }
}