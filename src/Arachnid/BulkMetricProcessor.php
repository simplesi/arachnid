<?php


namespace Arachnid;


use Arachnid\Model\Entity\PageRepository;
use Doctrine\ORM\EntityManager;

class BulkMetricProcessor
{
    /**
     * @var array
     */
    protected $batchProviders;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    public function __construct($batchProviders, EntityManager $em)
    {
        $this->em = $em;
        $this->batchProviders = $batchProviders;
        $this->pageRepository = $em->getRepository('\Arachnid\Model\Entity\Page');
    }

    public function process()
    {
        foreach($this->batchProviders as $provider)
        {
            $results = $provider->getResults();

            $count = 0;
            foreach($results as $url => $metrics)
            {
                $count++;
                $page = $this->pageRepository->findOrCreateOneByUrl($url);

                $page->addMetrics($metrics, $this->em);

                if ($count % 1000 == 0) {
                    echo "Saved $count records to database\n";
                    $this->em->flush();

                    // Save memory
                    $this->em->clear();
                }
                unset($results[$url]);
            }
            $this->em->flush();

            echo "Processed $count pages for {$provider->getName()}\n";
        }
    }

}