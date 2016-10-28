<?php

use Arachnid\ContentAnalysis\OnPage\ExtractHtmlMetadata;

include __DIR__.'/../vendor/autoload.php';

$appConfig = new \Arachnid\AppConfig();

$baseUrl = $argv[1];

if (isset($argv[2])) {
    $depth = $argv[2];
} else {
    $depth = 3;
}

$dataStore = new \Arachnid\DataStore\ORMDataStore($appConfig->getEntityManager());

$analysers = [
    new ExtractHtmlMetadata(),
    new \Arachnid\ContentAnalysis\OnPage\TextAnalysis(),
    new \Arachnid\ContentAnalysis\OnPage\ContentCounts()
];

// Initiate crawl
$crawler = new \Arachnid\Crawler([$baseUrl],$baseUrl, $depth, $dataStore, $analysers);
$crawler->crawl();

// Get link data
$links = $crawler->getLinks();
echo "Crawled ".count($links). " pages\n";
//print_r($links);