<?php

include __DIR__.'/../vendor/autoload.php';

$appConfig = new \Arachnid\AppConfig();

$googleAnalytics = new \Arachnid\ContentAnalysis\External\GoogleAnalytics(
    $appConfig->getSetting('google.analytics')
);

$processor = new \Arachnid\BulkMetricProcessor([$googleAnalytics], $appConfig->getEntityManager());
$processor->process();