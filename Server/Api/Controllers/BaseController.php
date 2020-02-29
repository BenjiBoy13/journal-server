<?php


namespace Server\Api\Controllers;


use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Server\Core\ORM;
use Server\Http\HttpRequest;

class BaseController
{
    private ORM $orm;
    public HttpRequest $httpRequest;

    public function __construct()
    {
        $this->orm = new ORM();
        $this->httpRequest = new HttpRequest();
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