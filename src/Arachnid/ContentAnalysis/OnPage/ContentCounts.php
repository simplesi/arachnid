<?php


namespace Arachnid\ContentAnalysis\OnPage;


use Symfony\Component\DomCrawler\Crawler;

class ContentCounts extends ContentAnalyser
{

    /**
     * Do the analysis on a piece of content
     * @param Crawler $content
     * @param $url
     * @return array
     */
    public function analyse(Crawler $content, $url)
    {
        $data = [];

        $actualContent = $this->getMainContentDOM($content);

        $data['count.image'] = $actualContent->filter('img')->count();

        // html5 video elements
        $data['count.video'] = $actualContent->filter('video')->count();

        // Or divs of class type-video
        $data['count.video'] += $actualContent->filter('div.type-video')->count();


        return $data;
    }

    /**
     * Return an array of the keys that might be returned in the result set (eg for csv columns)
     * @return array
     */
    public function getResultKeys()
    {
        return [
            'count.image',
            'count.video',
        ];
    }
}