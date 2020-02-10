<?php


namespace Server\Api\Controllers;


use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Server\Core\ORM;

class BaseController
{
    private ORM $orm;

    public function __construct()
    {
        $this->orm = new ORM();
    }

    /**
     * @return EntityManager
     * @throws DBALException
     * @throws ORMException
     */
    protected function getOrmManager (): EntityManager
    {
        return $this->orm->getEntityManager();
    }
}