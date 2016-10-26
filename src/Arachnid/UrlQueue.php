<?php


namespace Arachnid;

/**
 * Class UrlQueue
 * @package Arachnid
 *
 * Hold the urls we're visiting, de-duplicating, and queueing them
 */
class UrlQueue
{
    /**
     * @var \SplQueue
     */
    protected $urlQueue;
    protected $visitedUrls = [];
    protected $maxDepth;

    public function __construct($maxDepth)
    {
        $this->maxDepth = $maxDepth;
        $this->urlQueue = new \SplQueue();
    }

    /**
     * Are there urls in the queue?
     * @return bool
     */
    public function isEmpty()
    {
        return $this->urlQueue->isEmpty();
    }

    /**
     * Retrieve the next url, or null if there is none
     * @return UrlWithData
     */
    public function next()
    {
        if ($this->urlQueue->isEmpty()) return null;

        return $this->urlQueue->dequeue();
    }

    public function add($url, $depth)
    {
        // Don't accept urls greater than Max depth
        if ($depth > $this->maxDepth) return;

        $cleanedUrl = $this->cleanUrl($url);


        if (!$this->isCrawlable($cleanedUrl)) {
            return;
        }

        // We only want to queue urls that are new to us
        if (!array_key_exists($cleanedUrl, $this->visitedUrls))
        {
            $this->visitedUrls[$cleanedUrl] = true;

            $this->urlQueue->enqueue(new UrlWithData($cleanedUrl, $depth));
        }
    }

    public function addMultiple($urls, $depth)
    {
        foreach($urls as $url)
        {
            $this->add($url, $depth);
        }
    }

    protected function cleanUrl($uri)
    {
        return preg_replace('@#.*$@', '', $uri);
    }

    protected function isCrawlable($uri)
    {
        if (empty($uri) === true) {
            return false;
        }

        $stop_links = array(
            '@^javascript\:.*$@i',
            '@^#.*@',
            '@^mailto\:.*@i',
            '@^tel\:.*@i',
            '@^fax\:.*@i',
        );

        foreach ($stop_links as $ptrn) {
            if (preg_match($ptrn, $uri) === 1) {
                return false;
            }
        }

        return true;
    }
}