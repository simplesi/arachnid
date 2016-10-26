<?php

include __DIR__.'/../vendor/autoload.php';

$baseUrl = $argv[1];

if (count($argv) > 1) {
    $depth = $argv[2];
} else {
    $depth = 3;
}

$knownColumns = ['url', 'status_code', 'h1_count', 'h1_contents'];
$dataStore = new \Arachnid\DataStore\CSVDataStore( __DIR__.'/../data/',$knownColumns);

// Initiate crawl
$crawler = new \Arachnid\Crawler([$baseUrl],$baseUrl, $depth, $dataStore);
$crawler->crawl();

// Get link data
$links = $crawler->getLinks();
print_r($links);