<?php


namespace Arachnid\DataStore;


interface DataStore
{
    public function init($crawlId, $knownColumns);

    public function writeToStore($url, $data, $redirectUrls);

    public function close();
}