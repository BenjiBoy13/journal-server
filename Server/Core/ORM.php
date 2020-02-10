<?php


namespace Server\Core;


use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Yaml\Yaml;

class ORM
{
    private string $dbHost;
    private string $dbName;
    private string $dbUsername;
    private string $dbPassword;
    private string $dbDriver;

    public function __construct()
    {
        $ormSettings = Yaml::parseFile('./config/orm.yml');

        $dbSettings = $ormSettings['db'];

        $this->dbHost = $dbSettings['host'];
        $this->dbName = $dbSettings['database'];
        $this->dbUsername = $dbSettings['username'];
        $this->dbPassword = $dbSettings['password'];
        $this->dbDriver = $dbSettings['driver'];
    }

    /**
     * @return EntityManager
     * @throws DBALException
     * @throws ORMException
     */
    public function getEntityManager (): EntityManager
    {
        $config = Setup::createAnnotationMetadataConfiguration(array("./Server"), true, null, null, false);
        $connection = DriverManager::getConnection(array(
            'dbname' => $this->dbName,
            'user' => $this->dbUsername,
            'password' => $this->dbPassword,
            'host' => $this->dbHost,
            'driver' => $this->dbDriver
        ));

        return EntityManager::create($connection, $config);
    }
}