<?php


namespace Arachnid\DataStore;


interface DataStore
{
    public function init($crawlId);

    public function writeToStore($url, $data);

    public function close();
}