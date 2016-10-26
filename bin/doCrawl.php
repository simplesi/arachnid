<?php

use Arachnid\ContentAnalysis\OnPage\ExtractHtmlMetadata;

include __DIR__.'/../vendor/autoload.php';

$baseUrl = $argv[1];

if (count($argv) > 1) {
    $depth = $argv[2];
} else {
    $depth = 3;
}

$dataStore = new \Arachnid\DataStore\CSVDataStore( __DIR__.'/../data/');

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
print_r($links);