<?php


namespace Arachnid\DataStore;


use Arachnid\Model\Entity\PageRepository;
use Doctrine\ORM\EntityManager;

class ORMDataStore implements DataStore
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
        $this->pageRepository = $em->getRepository('\Arachnid\Model\Entity\Page');
    }

    public function init($crawlId, $knownColumns)
    {
    }

    public function writeToStore($url, $data)
    {
        $page = $this->pageRepository->findOrCreateOneByUrl($url);

        // Partition into floats (metrics) and strings (metadata)
        $metrics = [];
        $metadata = [];

        foreach($data as $key => $value)
        {
            if (is_float($value) || is_int($value))
            {
                $metrics[$key] = $value;
            } else if (is_string($value))
            {
                $metadata[$key] = $value;
            } else {
                throw new \InvalidArgumentException("Unsupported data type: ".gettype($value));
            }
        }

        $page->addMetrics($metrics, $this->entityManager);
        $page->addMetadatas($metadata, $this->entityManager);
        $this->entityManager->flush();
    }

    public function close()
    {
    }
}