<?php

use Arachnid\ContentAnalysis\OnPage\ExtractHtmlMetadata;

include __DIR__.'/../vendor/autoload.php';

$ga = new \Arachnid\ContentAnalysis\External\GoogleAnalytics();
print_r($ga->getForPage());