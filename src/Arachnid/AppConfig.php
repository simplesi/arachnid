<?php
namespace Arachnid;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\ArrayCache as Cache;
use Symfony\Component\Yaml\Parser;

class AppConfig
{
    /**
     * @var Connection
     */
    protected $dbalConnection;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected $config;

    /**
     * AppConfig constructor.
     * @param bool $loadReadOnlyDatabaseCopy
     *   Make a read-only copy of the database to load instead
     */
    public function __construct($loadReadOnlyDatabaseCopy = false)
    {
        $this->loadConfig();
        $this->loadDoctrine($loadReadOnlyDatabaseCopy);
    }

    protected function loadConfig()
    {
        // Read application Configuration
        $yaml = new Parser();

        $config = $yaml->parse(file_get_contents( __DIR__.'/../../conf/parameters.yml'));

        // Map %base_dir% to the base directory
        $baseDir = realpath(__DIR__.'/../../');
        $config = $this->replaceRecursive($config, '%base_dir%', $baseDir);

        setlocale(LC_ALL, $config['locale']);
        $this->config = $config;
    }

    function replaceRecursive($array, $oldValue, $newValue) {

        foreach ($array as $key => $value) {

            if (is_array($value)) {

                $array[$key] = $this->replaceRecursive($value, $oldValue, $newValue);

            } else {

                if(is_string($value)) {
                    $array[$key] = str_replace($oldValue, $newValue, $value);
                }
            }

        }
        return $array;
    }

    /**
     * Fetch a setting (or collection of settings) using a first.second.third syntax to walk the array structure
     * @param $name
     * @return mixed
     */
    public function getSetting($name)
    {
        $parts = explode('.', $name);

        $config = $this->config;
        foreach($parts as $part)
        {
            $config = $config[$part];
        }

        return $config;
    }

    function loadDoctrine($loadReadOnlyDatabaseCopy)
    {
        $databaseConfig = $this->getSetting('database');

        // Take a copy of the database to use for reporting
        if ($loadReadOnlyDatabaseCopy)
        {
            $roName = tempnam(dirname($databaseConfig['path']),'ro-db');
            copy($databaseConfig['path'],$roName);
            $databaseConfig['path'] = $roName;

            // Delete on script shutdown
            register_shutdown_function(function() use($databaseConfig) {unlink($databaseConfig['path']);});
        }

        // Doctrine DBAL
        $dbalconfig = new Configuration();
        $this->dbalConnection = DriverManager::getConnection($databaseConfig, $dbalconfig);

        // Doctrine ORM
        $ormconfig = new \Doctrine\ORM\Configuration();
        $cache = new Cache();
        $ormconfig->setQueryCacheImpl($cache);
        $ormconfig->setProxyDir(__DIR__ . '/Model/EntityProxy');
        $ormconfig->setProxyNamespace('EntityProxy');
        $ormconfig->setAutoGenerateProxyClasses(true);

        // ORM mapping by Annotation
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
            __DIR__ . '/../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
        $driver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(
            new \Doctrine\Common\Annotations\AnnotationReader(),
            array(__DIR__ . '/Model/Entity')
        );
        $ormconfig->setMetadataDriverImpl($driver);
        $ormconfig->setMetadataCacheImpl($cache);

        // EntityManager
        $this->entityManager = \Doctrine\ORM\EntityManager::create($databaseConfig,$ormconfig);

        // The Doctrine Classloader
        require __DIR__ . '/../../vendor/doctrine/common/lib/Doctrine/Common/ClassLoader.php';
        $classLoader = new \Doctrine\Common\ClassLoader('Entity', __DIR__ . '/Model');
        $classLoader->register();
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->dbalConnection;
    }

}