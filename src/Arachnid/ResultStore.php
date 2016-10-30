<?php


namespace Arachnid;

use Arachnid\DataStore\DataStore;

/**
 * Class ResultStore
 * @package Arachnid
 *
 * Hold the results for the crawl
 */
class ResultStore
{
    protected $data = [];

    /**
     * @var DataStore
     */
    protected $longTermStore = null;

    /**
     * ResultStore constructor.
     * @param DataStore|null $longTermDataStore
     */
    public function __construct($longTermDataStore = null, $crawlId, $resultColumns)
    {
        if ($longTermDataStore !== null) {
            $this->longTermStore = $longTermDataStore;
            $this->longTermStore->init($crawlId, $resultColumns);
        }
    }


    public function recordForUrl($url, $key, $value)
    {
        if (!array_key_exists($url, $this->data))
        {
            $data[$url] = [];
        }

        $this->data[$url][$key] = $value;
    }

    public function recordForUrlArray($url, $dataArray)
    {
        $existingData = [];

        if (array_key_exists($url, $this->data))
        {
            $existingData = $this->data[$url];
        }

        $dataForUrl = array_merge($existingData, $dataArray);
        $this->data[$url] = $dataForUrl;
    }

    public function recordError($url, $type, $message)
    {
        $payload = [
                'error_type' => $type,
                'error_message' => $message
            ];
        $this->recordForUrlArray($url, $payload);
    }

    public function recordUrlRedirects($url, $redirectList)
    {
        $this->recordForUrl($url, 'redirects', $redirectList);
    }


    public function markUrlComplete($url)
    {
        if ($this->longTermStore !== null)
        {
            $data = $this->data[$url];
            if (isset($data['redirects'])) {
                $redirects = $data['redirects'];
                unset($data['redirects']);
            } else {
                $redirects = [];
            }

            $this->longTermStore->writeToStore($url, $data, $redirects);
        }

        // TODO: clean up the local store?
    }

    public function getResults()
    {
        return $this->data;
    }
}