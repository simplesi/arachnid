<?php


namespace Arachnid\Export;


use Arachnid\Model\Entity\PageRepository;
use Doctrine\ORM\EntityManager;

class CSVExport
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->pageRepository = $em->getRepository('\Arachnid\Model\Entity\Page');
        $this->urlRepository = $em->getRepository('\Arachnid\Model\Entity\Url');
    }

    protected function getColumnNames()
    {
        $reportColumns = ['id' => 0,'url' => 1, 'status_code' => 2, 'redirect_urls' => 3];

        // Get distinct columns

        foreach(['metrics', 'metadata'] as $type) {
            $stmt = $this->em->getConnection()->query("select distinct(type) from $type");

            while($col = $stmt->fetch()) {
                if (!array_key_exists($col['type'], $reportColumns))
                {
                    $reportColumns[$col['type']] = count($reportColumns) + 1;
                }
            }
        }
        // Get the ordered list of report columns
        $reportColumnsOrdered = array_flip($reportColumns);

        return $reportColumnsOrdered;
    }

    /**
     * Returns the filename of the report
     * @return string
     */
    public function getReport()
    {
        $reportColumns = $this->getColumnNames();

        // Now export to csv once we know the distinct set of columns
        $filename = tempnam(sys_get_temp_dir(),'crawl-export-');
        $fp = fopen($filename, 'w');
        fputcsv($fp, $reportColumns);

        $q = $this->em->createQuery('select p from \Arachnid\Model\Entity\Page p order by p.id');
        $iterableResult = $q->iterate();
        foreach ($iterableResult as $pageRow) {
            $page = $pageRow[0];
            $pageData=[];
            $url = $page->getUrl();
            $pageData['id'] = $page->getId();
            $pageData['url'] = $url->getUrl();
            $pageData['status_code'] = $url->getStatusCode();

            $allUrls = $page->getAllUrls();
            if (count($allUrls)) {
                $urlStrings = [];
                foreach($allUrls as $redirectUrl)
                {
                    // Filter out urls which are the same as the eventual page url
                    if ($redirectUrl->getUrl() != $pageData['url']) {
                        $urlStrings[] = $redirectUrl->getUrl();
                    }
                    $this->em->detach($redirectUrl);
                }
                $pageData['redirect_urls'] = implode(' ', $urlStrings);
            }

            foreach($page->getMetadata() as $metadata)
            {
                $pageData[$metadata->getType()] = $metadata->getValue();
                $this->em->detach($metadata);
            }

            foreach($page->getMetrics() as $metric)
            {
                $pageData[$metric->getType()] = $metric->getValue();
                $this->em->detach($metric);
            }

            $row = [];
            foreach($reportColumns as $column)
            {
                if (isset($pageData[$column]))
                {
                    $row[] = $pageData[$column];
                } else {
                    $row[] = null;
                }
            }
            fputcsv($fp, $row);

            // Clean up memory
            $this->em->detach($page);
            $this->em->detach($url);

        }

        fclose($fp);

        return $filename;
    }
}