<?php

namespace Arachnid;

use Arachnid\Client\RecordingClient;
use Arachnid\DataStore\DataStore;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * Crawler
 *
 * This class will crawl all unique internal links found on a given website
 * up to a specified maximum page depth.
 *
 * This library is based on the original blog post by Zeid Rashwani here:
 *
 * <http://zrashwani.com/simple-web-spider-php-goutte>
 *
 * Josh Lockhart adapted the original blog post's code (with permission)
 * for Composer and Packagist and updated the syntax to conform with
 * the PSR-2 coding standard.
 *
 * @package Crawler
 * @author  Josh Lockhart <https://github.com/codeguy>
 * @author  Zeid Rashwani <http://zrashwani.com>
 * @version 1.0.4
 */
class Crawler
{
    /**
     * The set of urls we're planning on visiting
     * @var UrlQueue
     */
    protected $urlStore;

    /**
     * Only crawl urls within this url path
     * @var string
     */
    protected $baseUrl;

    /**
     * Unique ID for this crawl
     * @var string
     */
    protected $crawlId;

    /*
     *
     */
    protected $analysers;

    /**
     * Constructor
     * @param array $initialUrls
     * @param string $baseUrl
     * @param int    $maxDepth
     * @param DataStore $dataStore
     */
    public function __construct(array $initialUrls, $baseUrl, $maxDepth = 3, $dataStore = null, $analysers = [])
    {
        // Generate an ID for this crawl
        $date = new \DateTime();
        $this->crawlId = $date->format(\DateTime::ISO8601);

        $this->urlStore = new UrlQueue($maxDepth);
        foreach($initialUrls as $url)
        {
            $this->urlStore->add($url, 1);
        }


        $this->analysers = $analysers;

        $this->resultStore = new ResultStore($dataStore, $this->crawlId, $this->getResultColumns());
        $this->baseUrl = $baseUrl;

    }

    /**
     * Run the crawl
     */
    public function crawl()
    {
        while(!$this->urlStore->isEmpty()){
            $this->crawlSingle($this->urlStore->next());
        }
    }

    /**
     * Get links (and related data) found by the crawler
     * @return array
     */
    public function getLinks()
    {
        return $this->resultStore->getResults();
    }

    /**
     * Crawl single URL
     * @param UrlWithData $urlWithData
     */
    protected function crawlSingle(UrlWithData $urlWithData)
    {
        $url = $urlWithData->getUrl();
        $client = $this->getScrapClient();

        try {

            $crawler = $client->request('GET', $url);

            $url = $crawler->getUri();

            $statusCode = $client->getResponse()->getStatus();
            $this->resultStore->recordForUrl($url, 'status_code', $statusCode);
            $this->resultStore->recordUrlRedirects($url, $client->getRedirectList());

            if ($statusCode === 200) {
                $content_type = $client->getResponse()->getHeader('Content-Type');

                if (strpos($content_type,'text/html') !== false) {
                    //traverse children in case the response in HTML document only

                    if ($this->shouldExtractLinksInUri($url)) {
                        $childLinks = $this->extractLinksInfo($crawler, $url);
                        $this->urlStore->addMultiple($childLinks, $urlWithData->getDepth() + 1);
                    }
                    $this->analysePage($crawler, $url);
                }
            }
        } catch (ConnectException $e){
            $this->resultStore->recordError($url, 'Connection', $e->getMessage());

        } catch (BadResponseException $e) {
            $this->resultStore->recordError($url, $e->getResponse()->getStatusCode(), $e->getMessage());

        } catch (TooManyRedirectsException $e) {
            $this->resultStore->recordError($url, 'TooManyRedirects', $e->getMessage());

            $redirectsSeen = [];
            $trimmedRedirectList = [];

            // Unique the redirects which are likely cyclic
            foreach($client->getRedirectList() as $redirect)
            {
                list($status, $url) = $redirect;
                if (!isset($redirectsSeen[$url]))
                {
                    // Flip the status so we can see it ends in an error
                    $trimmedRedirectList[] = [ -1 * $status, $url];
                }
            }

            $this->resultStore->recordUrlRedirects($url, $trimmedRedirectList);
        }

        $this->resultStore->markUrlComplete($url);
    }

    protected function shouldExtractLinksInUri($uri)
    {
        return !$this->checkIfExternal($uri);
    }

    /**
     * create and configure goutte client used for scraping
     * @return RecordingClient
     */
    protected function getScrapClient()
    {
        $client = new RecordingClient();
        $client->followRedirects();
        $client->setMaxRedirects(30);

        $guzzleClient = new \GuzzleHttp\Client(array(
            'curl' => array(
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30
            ),
        ));
        $client->setClient($guzzleClient);

        return $client;
    }

    /**
     * Extract links information from url
     * @param  \Symfony\Component\DomCrawler\Crawler $crawler
     * @param  string                                $url
     * @return array
     */
    protected function extractLinksInfo(DomCrawler $crawler, $url)
    {
        $childLinks = array();
        $crawler->filter('a')->each(function (DomCrawler $node, $i) use (&$childLinks, $url) {

            $node_url = $node->attr('href');

            // Ensure we get an absolute url
            if (preg_match("@^http(s)?@", $node_url) !== 1) {
                if (strpos($node_url, '/') === 0) {
                    $parsed_url = parse_url($url);
                    $node_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $node_url;
                } else {
                    $node_url = substr($url, 0, strrpos($url, '/')) . '/' . $node_url;
                }
            }

            // TODO: Do something with the url text?
            //$node_text = trim($node->text());

            $childLinks[] = $node_url;

        });

        return $childLinks;
    }

    /**
     * Extract information from page content
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     * @param string                                $url
     */
    protected function analysePage(DomCrawler $crawler, $url)
    {
        $data = [];
        foreach ($this->analysers as $analyser)
        {
            $data = array_merge($data, $analyser->analyse($crawler, $url));
        }

        $this->resultStore->recordForUrlArray($url, $data);
    }

    protected function getResultColumns()
    {
        $columns = ['url', 'status_code'];

        foreach($this->analysers as $analyser) {
            foreach($analyser->getResultKeys() as $key)
            $columns[] = $key;
        }
        return $columns;
    }

    /**
     * Is URL external?
     * @param  string $url An absolute URL (with scheme)
     * @return bool
     */
    protected function checkIfExternal($url)
    {
        $base_url_trimmed = str_replace(array('http://', 'https://'), '', $this->baseUrl);

        $base_url_trimmed = explode('/', $base_url_trimmed)[0];

        return preg_match("@http(s)?\://$base_url_trimmed@", $url) !== 1;
    }

}
