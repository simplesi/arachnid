<?php


namespace Arachnid;

/**
 * Class ResultStore
 * @package Arachnid
 *
 * Hold the results for the crawl
 */
class ResultStore
{
    protected $data = [];


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

    public function getResults()
    {
        return $this->data;
    }
}