<?php

include __DIR__.'/../vendor/autoload.php';

$appConfig = new \Arachnid\AppConfig();
$csvExport = new \Arachnid\Export\CSVExport($appConfig->getEntityManager());

set_time_limit(600);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=crawl.csv');

readfile($csvExport->getReport());