<?php


namespace Arachnid\DataStore;


use Arachnid\Model\Entity\PageRepository;
use Arachnid\Model\Entity\UrlRepository;
use Doctrine\ORM\EntityManager;

class ORMDataStore implements DataStore
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var UrlRepository
     */
    protected $urlRepository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
        $this->pageRepository = $em->getRepository('\Arachnid\Model\Entity\Page');
        $this->urlRepository = $em->getRepository('\Arachnid\Model\Entity\Url');
    }

    public function init($crawlId, $knownColumns)
    {
    }

    public function writeToStore($url, $data, $redirectUrls)
    {
        $statusCode = 0;

        if (isset($data['status_code']))
        {
            $statusCode = $data['status_code'];
            unset($data['status_code']);
        }

        $url = $this->urlRepository->findOrCreateOne($url, $statusCode);

        $page = $this->pageRepository->findOrCreateOneByUrl($url);

        $url->addPage($page);

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
                if ($value != null ) {
                    throw new \InvalidArgumentException("Unsupported data type: " . gettype($value));
                }
            }
        }

        $page->addMetrics($metrics, $this->entityManager);
        $page->addMetadatas($metadata, $this->entityManager);

        // Do redirects, walking backwards from the one that hit the page
        $redirectsReversed = array_reverse($redirectUrls);

        $nextUrl = $url;
        foreach($redirectsReversed as $redirect)
        {
            list($redirectStatus, $redirectUrlPath) = $redirect;

            // Skip the final url, which will be the first entry and isn't a redirect
            if ($redirectUrlPath == $url->getUrl()) continue;

            // If it's just a http -> https redirect, we don't care
            if (substr($redirectUrlPath,0,5) == "http:"  && substr($nextUrl->getUrl(),0,5) == "https"
                && substr($redirectUrlPath, 7) == substr($nextUrl->getUrl(),8))
            {
                continue;
            }

            $redirectUrl = $this->urlRepository->findOrCreateOne($redirectUrlPath, $redirectStatus);
            $redirectUrl->addRedirectTo($nextUrl);
            $redirectUrl->addPage($page);

            $nextUrl = $redirectUrl;
        }

        $this->entityManager->flush();
    }

    public function close()
    {
    }
}