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
        $this->pageRepository = $em->getRepository('Page');
    }

    public function init($crawlId, $knownColumns)
    {
    }

    public function writeToStore($url, $data)
    {
        $page = $this->pageRepository->findOrCreateOneByUrl($url);

        $page->addMetrics($data);
        $this->entityManager->flush();
    }

    public function close()
    {
    }
}