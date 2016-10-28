<?php

use Symfony\Component\Console\Helper\HelperSet,
    Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper,
    Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper,
    Doctrine\ORM\Tools\Console\ConsoleRunner;

include __DIR__.'/../vendor/autoload.php';

$appConfig = new \Arachnid\AppConfig();

$helperSet = new HelperSet(array(
    'em' => new EntityManagerHelper($appConfig->getEntityManager()),
    'conn' => new ConnectionHelper($appConfig->getEntityManager()->getConnection())
));

ConsoleRunner::run($helperSet);