<?php

include __DIR__.'/../vendor/autoload.php';

$appConfig = new \Arachnid\AppConfig();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=crawl.csv');

readfile($appConfig->getSetting('report.csvExportPath'));