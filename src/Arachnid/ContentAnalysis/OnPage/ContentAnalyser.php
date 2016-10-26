<?php


namespace Arachnid\ContentAnalysis\OnPage;


use Symfony\Component\DomCrawler\Crawler;

abstract class ContentAnalyser
{
    /**
     * Do the analysis on a piece of content
     * @param Crawler $content
     * @param $url
     * @return array
     */
    abstract public function analyse(Crawler $content, $url);

    /**
     * Return an array of the keys that might be returned in the result set (eg for csv columns)
     * @return array
     */
    abstract public function getResultKeys();

    protected function getMainContentDOM(Crawler $content)
    {
        // If it's a Drupal page, this should be the text
        $actualContent = $content->filter('div.l-content');

        if ($actualContent->count() == 0)
        {
            // If an old press release...
            $actualContent = $content->filter('#contentrightpr');
        }

        return $actualContent;
    }

}