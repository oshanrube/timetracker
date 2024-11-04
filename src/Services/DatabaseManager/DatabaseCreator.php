<?php

namespace App\Services\DatabaseManager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 *
 */
class DatabaseCreator
{
    /**
     * @param ParameterBagInterface $params
     * @param ContainerInterface    $container
     */
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly ContainerInterface    $container,
    ) {
    }

    /**
     * @param int $company_id
     *
     * @return Connection
     */
    public function createDatabaseConnection(int $company_id): Connection
    {
        $params  = [
            'url'                 => sprintf($this->params->get('app.company_database_url_template'), $company_id),
            'use_savepoints'      => TRUE, 'driver' => 'pdo_mysql',
            'idle_connection_ttl' => 600, 'host' => 'localhost',
            'port'                => NULL, 'user' => 'root', 'password' => NULL,
            'driverOptions'       => [], 'defaultTableOptions' => [],
        ];

        $config             = $this->container->get('doctrine.dbal.company_connection.configuration');
        $event_manager      = $this->container->get('doctrine.dbal.company_connection.event_manager');
        $connection_factory = $this->container->get('doctrine.dbal.connection_factory');
        $connection         = $connection_factory->createConnection($params, $config, $event_manager);
        $this->container->set('doctrine.dbal.company_connection_'.$company_id, $connection);

        return $connection;
    }

    public function loadDatabaseConnection(int $company_id): Connection
    {
        if(!$this->container->has('doctrine.dbal.company_connection_'.$company_id))
        {
            return $this->createDatabaseConnection($company_id);
        }
        return $this->container->has('doctrine.dbal.company_connection_'.$company_id);
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
        $company_configuration = $this->container->get('doctrine.orm.company_configuration');
        $event_manager         = $this->container->get('doctrine.dbal.company_connection.event_manager');
        $entity_manager        = new EntityManager($connection, $company_configuration, $event_manager);
        $this->container->set('doctrine.orm.company_entity_manager', $entity_manager);
    }

    /**
     * @return void
     */
    public function createDatabaseSchema(): void
    {
        // get entity manager
        $entity_manager = $this->container->get('doctrine.orm.company_entity_manager');
        // create schema tool
        $st = new SchemaTool($entity_manager);
        // update schema
        $meta_data      = $entity_manager->getMetadataFactory()->getAllMetadata();
        $st->updateSchema($meta_data);
    }

    /**
     * @param int $company_id
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function loadDatabase(int $company_id): void
    {
        // create connection
        $connection = $this->createDatabaseConnection($company_id);
        // create Database
        $this->createDatabaseIfNotExists($connection);
        // create entityManager
        $this->createEntityManager($connection);
        // create database tables
        $this->createDatabaseSchema();
    }
}