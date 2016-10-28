<?php


namespace Arachnid\ContentAnalysis\External;


use Arachnid\ContentAnalysis\BatchMetrics;

class GoogleAnalytics implements BatchMetrics
{
    protected $tableId;
    protected $gaClient;

    protected $aggregatedResults = [];

    protected $aggregateUrlPrefixes;

    public function __construct($options) {

        $this->tableId = $options['table'];
        $this->aggregateUrlPrefixes = $options['aggregatePrefixes'];
        $this->maxResults = $options['maxResults'];

        $client = new \Google_Client();
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $client->setAuthConfigFile(__DIR__.'/../../../../conf/google-auth.json');

        $this->gaClient = new \Google_Service_Analytics($client);
    }

    public function getName()
    {
        return 'Google Analytics';
    }
    public function getResults(){

        $optParams = array(
            'dimensions' => 'ga:pagePath',
            'max-results' => '10000',
            'sort' => 'ga:pagePath'
            );

        $tableId = $this->tableId;
        $dateFrom = '2014-10-20';
        $dateTo = '2016-10-20';
        $metrics = 'ga:bounceRate,ga:pageviews,ga:avgTimeOnPage,ga:entrances,ga:exitRate,ga:avgPageLoadTime,ga:avgDomInteractiveTime';

        // First get the result count
        $headerResults = $this->fetchPageOfMetrics($tableId, $dateFrom, $dateTo, $optParams, $metrics, 1, 1);
        $totalResults = $headerResults['totalResults'];

        // Work out the column headings
        $header = array_map(function($elem){return $elem['name'];}, $headerResults['modelData']['columnHeaders']);
        $nameToColNum = [];
        foreach($header as $num => $colName)
        {
            $nameToColNum[$colName] = $num;
        }

        $pageSize = 10000;
        $resultRow = 1;

        if ($totalResults > $this->maxResults){
            $totalResults = $this->maxResults;
        }

        while($resultRow < $totalResults) {
            echo "Fetching row $resultRow\n";
            $partialResults = $this->fetchPageOfMetrics(
                $tableId, $dateFrom, $dateTo, $optParams, $metrics, $resultRow, $pageSize
            );

            $this->aggregateIntoResults($partialResults['rows'], $nameToColNum);
            $resultRow += $pageSize;
        }

        return $this->aggregatedResults;
    }

    protected function aggregateIntoResults($resultsPage, $nameToColNum)
    {
        // Roll up the data for each unique url
        foreach ($resultsPage as $result) {
            $url = $result[$nameToColNum['ga:pagePath']];
            $urlWithoutParams = $this->stripQueryUrl($url);

            $newData = array_combine(array_keys($nameToColNum),$result);
            // remove url field
            unset($newData['ga:pagePath']);

            if (!array_key_exists($urlWithoutParams, $this->aggregatedResults))
            {
                if (isset($newData['ga:pageviews']) && $newData['ga:pageviews'] != 0) {
                    // Don't add pages with no views
                    $this->aggregatedResults[$urlWithoutParams] = $newData;
                }
            } else {

                $combinedData =[];
                $existingData = $this->aggregatedResults[$urlWithoutParams];

                $existingWeight = $existingData['ga:pageviews'];
                $newWeight = $newData['ga:pageviews'];

                foreach($newData as $colName => $newValue)
                {
                    $existingValue = $existingData[$colName];

                    $combined = '';
                    switch ($colName)
                    {
                        case 'ga:sessions':
                        case 'ga:pageviews':
                        case 'ga:entrances':
                            // Sum the two
                            $combined = $existingValue + $newValue;
                            break;
                        case 'ga:bounceRate':
                        case 'ga:sessionDuration':
                        case 'ga:avgTimeOnPage':
                        case 'ga:exitRate':
                        case 'ga:avgPageLoadTime':
                        case 'ga:avgDomInteractiveTime':
                            // Weighted average
                            if (($existingWeight + $newWeight) == 0 )
                            {
                                $combined = 0;
                            } else {
                                $combined =
                                    (($existingWeight * $existingValue) + ($newValue * $newWeight))
                                    / ($existingWeight + $newWeight);
                            }
                            break;
                        default:
                            throw new \InvalidArgumentException('Unknown metric $colname, please add to aggregation code');
                    }

                    $combinedData[$colName] = $combined;
                }
                $this->aggregatedResults[$urlWithoutParams] = $combinedData;
            }
        }

    }

    protected function stripQueryUrl($url)
    {
        // For some urls, we aggregate all sub-paths to reduce noise
        foreach($this->aggregateUrlPrefixes as $prefix)
        {
            if (strpos($url, $prefix) !== FALSE )
            {
                $url = $prefix.' (aggregated) ';
                break;
            }

        }

        $url = strtok($url, '?');

        // Make it a url, rather than a GA path, which begins /.
        return 'https:/'.$url;
    }

    protected function fetchPageOfMetrics($tableId, $dateFrom, $dateTo, $optParams, $metrics, $resultFrom, $maxResults)
    {
        $params = $optParams + [ 'start-index' => $resultFrom, 'max-results' => $maxResults];

        $data = $this->gaClient->data_ga->get(
            $tableId,
            $dateFrom,
            $dateTo,
            $metrics,
            $params
        );

        return $data;
    }
}