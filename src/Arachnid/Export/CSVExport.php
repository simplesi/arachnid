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

    public function getReport()
    {
        $reportData = [];
        $reportColumns = ['id' => 0,'url' => 1, 'status_code' => 2, 'redirect_urls' => 3];
        $pages = $this->pageRepository->findBy([],['id' => 'ASC']);
        foreach($pages as $page)
        {
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
                if (!isset($reportColumns[$metadata->getType()])) $reportColumns[$metadata->getType()] = count($reportColumns) + 1;
            }

            foreach($page->getMetrics() as $metric)
            {
                $pageData[$metric->getType()] = $metric->getValue();
                if (!isset($reportColumns[$metric->getType()])) $reportColumns[$metric->getType()] = count($reportColumns) + 1;
            }
            $reportData[] = $pageData;
        }

        // Get the ordered list of report columns
        $reportColumnsOrdered = array_flip($reportColumns);

        // Now export to csv once we know the distinct set of columns
        $filename = tempnam(sys_get_temp_dir(),'crawl-export-');
        $fp = fopen($filename, 'w');
        fputcsv($fp, $reportColumnsOrdered);
        foreach($reportData as $rowData)
        {
            $row = [];
            foreach($reportColumnsOrdered as $column)
            {
                if (isset($rowData[$column]))
                {
                    $row[] = $rowData[$column];
                } else {
                    $row[] = null;
                }
            }
            fputcsv($fp, $row);
        }

        fclose($fp);

        $reportString =  file_get_contents($filename);
        unlink($filename);

        return $reportString;
    }
}