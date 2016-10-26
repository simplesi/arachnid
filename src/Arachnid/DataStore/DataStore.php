<?php


namespace Arachnid\DataStore;


interface DataStore
{
    public function init($crawlId, $knownColumns);

    public function writeToStore($url, $data);

    public function close();
}