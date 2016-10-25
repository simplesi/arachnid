<?php

include __DIR__.'/../vendor/autoload.php';

$baseUrl = $argv[1];

if (count($argv) > 1) {
    $depth = $argv[2];
}

// Initiate crawl
$crawler = new \Arachnid\Crawler($baseUrl, $depth);
$crawler->traverse();

// Get link data
$links = $crawler->getLinks();
print_r($links);