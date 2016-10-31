<?php

include __DIR__.'/../vendor/autoload.php';

$appConfig = new \Arachnid\AppConfig();
$csvExport = new \Arachnid\Export\CSVExport($appConfig->getEntityManager());

echo $csvExport->getReport();