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
            $stmt = $this->em->getConnection()->query("select distinct type from $type");
            $cols = $stmt->fetchAll();

            foreach ($cols as $col) {
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
                foreach($allUrls as $url)
                {
                    // Filter out urls which are the same as the eventual page url
                    if ($url->getUrl() != $pageData['url']) {
                        $urlStrings[] = $url->getUrl();
                    }
                }
                $pageData['redirect_urls'] = implode(' ', $urlStrings);
            }

            foreach($page->getMetadata() as $metadata)
            {
                $pageData[$metadata->getType()] = $metadata->getValue();
            }

            foreach($page->getMetrics() as $metric)
            {
                $pageData[$metric->getType()] = $metric->getValue();
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
        }

        fclose($fp);

        return $filename;
    }
}