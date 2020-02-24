<?php

require_once './vendor/autoload.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Server\Core\ORM;

$dotEnv = new Symfony\Component\Dotenv\Dotenv();
$dotEnv->load(__DIR__ . "/.env");

$appOrm = new ORM();

return ConsoleRunner::createHelperSet($appOrm->getEntityManager());