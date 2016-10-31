<?php

include __DIR__.'/../vendor/autoload.php';

$appConfig = new \Arachnid\AppConfig(true);
$csvExport = new \Arachnid\Export\CSVExport(
    $appConfig->getEntityManager(),
    $appConfig->getSetting('report.csvExportPath')
);
$csvExport->buildReport();