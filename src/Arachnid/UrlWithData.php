<?php


namespace Arachnid;


class UrlWithData
{
    protected $url;

    protected $depth;

    public function __construct($url, $depth)
    {
        $this->url = $url;
        $this->depth = $depth;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

}