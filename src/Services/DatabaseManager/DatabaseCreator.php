<?php

namespace App\Services\DatabaseManager;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ContainerAwareEventManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 *
 */
class DatabaseCreator
{
    public function __construct(
        private readonly string                      $company_database_url_template,
        private readonly Configuration               $company_connection_configuration,
        private readonly ContainerAwareEventManager  $company_connection_event_manager,
        private readonly ConnectionFactory           $connection_factory,
        private readonly \Doctrine\ORM\Configuration $company_configuration,
        private                                      $company_entity_manager,
        private readonly LoggerInterface             $logger,
    ) {
    }

    /**
     * @param int $company_id
     *
     * @return Connection
     */
    public function createDatabaseConnection(int $company_id): Connection
    {
        $params = [
            'url'                 => sprintf($this->company_database_url_template, $company_id),
            'use_savepoints'      => TRUE, 'driver' => 'pdo_mysql',
            'idle_connection_ttl' => 600, 'host' => 'localhost',
            'port'                => NULL, 'user' => 'root', 'password' => NULL,
            'driverOptions'       => [], 'defaultTableOptions' => [],
        ];

        return $this->connection_factory->createConnection($params, $this->company_connection_configuration, $this->company_connection_event_manager);
    }

    /**
     * @param Connection $connection
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function createDatabaseIfNotExists(Connection $connection): void
    {
        $params = $connection->getParams();
        // replace with prefix
        $db_name = $params['dbname'];
        unset($params['dbname'], $params['path'], $params['url']);
        $tmpConnection = DriverManager::getConnection($params, $connection->getConfiguration());
        $schemaManager = $tmpConnection->createSchemaManager();
        if (!in_array($db_name, $schemaManager->listDatabases())) {
            $schemaManager->createDatabase($db_name);
        }
    }

    /**
     * @param Connection $connection
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteDatabaseIfExists(Connection $connection): void
    {
        $params = $connection->getParams();
        // replace with prefix
        $db_name = $params['dbname'];
        unset($params['dbname'], $params['path'], $params['url']);
        $tmpConnection = DriverManager::getConnection($params, $connection->getConfiguration());
        $schemaManager = $tmpConnection->createSchemaManager();
        if (in_array($db_name, $schemaManager->listDatabases())) {
            $schemaManager->dropDatabase($db_name);
        }
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    public function createEntityManager(Connection $connection): void
    {
        $entity_manager = new EntityManager($connection, $this->company_configuration, $this->company_connection_event_manager);
        //$this->container->set('doctrine.orm.company_entity_manager', $entity_manager);
        $this->company_entity_manager = $entity_manager;
    }

    /**
     * @return void
     */
    public function createDatabaseSchema(): void
    {
        // create schema tool
        $st = new SchemaTool($this->company_entity_manager);
        // update schema
        $meta_data = $this->company_entity_manager->getMetadataFactory()->getAllMetadata();
        $st->updateSchema($meta_data);
    }

    /**
     * @param int $company_id
     *
     * @return EntityManager
     * @throws \Doctrine\DBAL\Exception
     */
    public function loadDatabase(int $company_id): EntityManager
    {
        try {
            // create connection
            $connection = $this->createDatabaseConnection($company_id);
            // create Database
            $this->createDatabaseIfNotExists($connection);
            // create entityManager
            $this->createEntityManager($connection);
            // create database tables
            $this->createDatabaseSchema();

            return $this->company_entity_manager;
        } catch (\Exception $exception) {
            $this->logger->error("[DatabaseCreator|loadDatabase]: {$exception->getMessage()}");
        }
    }

    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}