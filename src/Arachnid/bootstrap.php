<?php

use Doctrine\Common\Cache\ArrayCache as Cache;
use Symfony\Component\Yaml\Parser;

require_once __DIR__ . '/../../vendor/autoload.php';

// Read application Configuration
$yaml = new Parser();

$config = $yaml->parse(file_get_contents( __DIR__.'/../../conf/parameters.yml'));
$config['database']['path'] = __DIR__ . '/../../'.$config['database']['path'];

setlocale(LC_ALL, $config['locale']);

// Doctrine DBAL
$dbalconfig = new Doctrine\DBAL\Configuration();
$conn = Doctrine\DBAL\DriverManager::getConnection($config['database'], $dbalconfig);

// Doctrine ORM
$ormconfig = new Doctrine\ORM\Configuration();
$cache = new Cache();
$ormconfig->setQueryCacheImpl($cache);
$ormconfig->setProxyDir(__DIR__ . '/Model/EntityProxy');
$ormconfig->setProxyNamespace('EntityProxy');
$ormconfig->setAutoGenerateProxyClasses(true);

// ORM mapping by Annotation
Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
    __DIR__ . '/../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
$driver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver(
    new Doctrine\Common\Annotations\AnnotationReader(),
    array(__DIR__ . '/Model/Entity')
);
$ormconfig->setMetadataDriverImpl($driver);
$ormconfig->setMetadataCacheImpl($cache);

// EntityManager
$em = Doctrine\ORM\EntityManager::create($config['database'],$ormconfig);

// The Doctrine Classloader
require __DIR__ . '/../../vendor/doctrine/common/lib/Doctrine/Common/ClassLoader.php';
$classLoader = new Doctrine\Common\ClassLoader('Entity', __DIR__ . '/Model');
$classLoader->register();