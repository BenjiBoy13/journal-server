<?php


namespace Server\Core;


use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Yaml\Yaml;

/**
 * ------------------------------------------------------
 * Class ORM
 * ------------------------------------------------------
 *
 * Handles connection between the application and the
 * ORM Manager that this software uses, in this case
 * ( Doctrine ORM )
 *
 * @author Benjamin Gil Flores
 * @version NaN
 * @package Server\Core
 */
class ORM
{
    /**
     * @var string Host name of the database server
     */
    private string $dbHost;

    /**
     * @var string Name of the database
     */
    private string $dbName;

    /**
     * @var string Username of the database server
     */
    private string $dbUsername;

    /**
     * @var string Password of the database server
     */
    private string $dbPassword;

    /**
     * @var string PHP Driver to handle the database interaction
     */
    private string $dbDriver;

    /**
     * ORM constructor.
     *
     * Retrieves ORM configuration file variables and
     * assigns them to class properties
     */
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
     * Retrieves the Doctrine ORM Entity Manager and
     * establishes a connection with configured database
     *
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