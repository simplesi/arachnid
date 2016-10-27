<?php


namespace Arachnid\ContentAnalysis\External;


class GoogleAnalytics
{
    protected $gaClient;

    public function __construct() {
        $client = new \Google_Client();
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $client->setAuthConfigFile(__DIR__.'/../../../../conf/google-auth.json');

        $this->gaClient = new \Google_Service_Analytics($client);


    }

    public function getForPage(){

        $optParams = array(
            'dimensions' => 'ga:pagePath',
            'max-results' => '100');


        $data = $this->gaClient->data_ga->get(
            'ga:4681116',
            '2014-01-01',
            '2016-01-15',
            'ga:sessions,ga:bounceRate,ga:sessionDuration,ga:pageviews,ga:avgTimeOnPage',
            $optParams
        );

        return $data;
    }

    /**
     * view ga:4681116
     *
     * metrics
     * ga:sessions
    ga:bounceRate
    ga:sessionDuration
    ga:pageviews
    ga:avgTimeOnPage

     * dimensions
    ga:pagePath

     * need to page through results with start-index and max-results


     *
     */
    // https://www.googleapis.com/analytics/v3/data/ga?ids=ga%3A4681116&start-date=2014-10-01&end-date=yesterday&metrics=ga%3Asessions%2Cga%3AbounceRate%2Cga%3AsessionDuration%2Cga%3Apageviews%2Cga%3AavgTimeOnPage&dimensions=ga%3ApagePath&include-empty-rows=false&max-results=10000&access_token=ya29.CjCIA7TvJYFt6jVI6w9o5wRqEpxSkmMXBnpbyRBz5YY9QxskUv7RrQHeJMZVmKUClBo

}