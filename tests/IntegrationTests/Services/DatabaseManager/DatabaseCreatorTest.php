<?php

namespace App\Tests\IntegrationTests\Services\DatabaseManager;

use App\Entity\Company\Company;
use App\Services\DatabaseManager\DatabaseCreator;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DatabaseCreatorTest extends KernelTestCase
{
    private static function createDatabaseCreator()
    {
        return new DatabaseCreator(
            self::getContainer()->getParameter('app.company_database_url_template'),
            self::getContainer()->get('doctrine.dbal.company_connection.configuration'),
            self::getContainer()->get('doctrine.dbal.company_connection.event_manager'),
            self::getContainer()->get('doctrine.dbal.connection_factory'),
            self::getContainer()->get('doctrine.orm.company_configuration'),
            self::getContainer()->get('doctrine.orm.company_entity_manager'),
            self::getContainer()->get('logger'),
        );
    }

    public function testCreateDatabaseConnection()
    {
        $this->expectNotToPerformAssertions();
        $db_creator = self::createDatabaseCreator();

        $company_id = 233;
        // create connection
        $db_creator->createDatabaseConnection($company_id);
    }

    public function testDeleteDatabaseIfExistsWithoutDb()
    {
        $this->expectNotToPerformAssertions();
        $db_creator = self::createDatabaseCreator();

        $company_id = 233;
        // create connection
        $connection = $db_creator->createDatabaseConnection($company_id);
        // delete Database
        $db_creator->deleteDatabaseIfExists($connection);
    }

    public function testDeleteDatabaseIfExistsWithDb()
    {
        $this->expectNotToPerformAssertions();
        $db_creator = self::createDatabaseCreator();

        $company_id = 233;
        // create connection
        $connection = $db_creator->createDatabaseConnection($company_id);
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
        $db_creator = self::createDatabaseCreator();

        $company_id = 233;
        // create connection
        $connection = $db_creator->createDatabaseConnection($company_id);
        // create Database
        $db_creator->createDatabaseIfNotExists($connection);
    }

    /**
     * @throws Exception
     */
    public function testLoadDatabaseFromCompanyId()
    {
        $db_creator = self::createDatabaseCreator();

        $company_id = 233;
        // create database tables
//        $db_creator->setContainer(self::getContainer()->get('service_container'));
        $company_entity_manager = $db_creator->loadDatabase($company_id);
//        self::getContainer()->set('doctrine.orm.company_entity_manager', $company_entity_manager);
        // try creating a new Entity
        $company = new Company();
        $company->setId($company_id);
        $company->setName("New Company name");

        $company_entity_manager->persist($company);
        $company_entity_manager->flush();

        $company = $company_entity_manager
            ->getRepository(Company::class)
            ->find($company_id);
        $this->assertNotNull($company);
    }
}