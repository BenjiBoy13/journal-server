<?php

require_once './vendor/autoload.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Server\Core\ORM;

$appOrm = new ORM();

return ConsoleRunner::createHelperSet($appOrm->getEntityManager());