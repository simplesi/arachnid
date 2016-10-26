<?php


namespace Arachnid\ContentAnalysis\OnPage;


use DaveChild\TextStatistics\TextStatistics;
use Symfony\Component\DomCrawler\Crawler;

class TextAnalysis extends ContentAnalyser
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

        if ($actualContent->count()){
            $text = $actualContent->text();
        } else {
            $text = '';
        }


        $stats = new TextStatistics();
        $data['flesch-kincaid'] = $stats->flesch_kincaid_reading_ease($text);
        $data['wordcount'] = $stats->wordCount($text);
        $data['sentencecount'] = $stats->sentenceCount($text);
        $data['contentsize'] = strlen($content->html());

        return $data;
    }

    /**
     * Return an array of the keys that might be returned in the result set (eg for csv columns)
     * @return array
     */
    public function getResultKeys()
    {
        return [ 'wordcount', 'sentencecount','flesch-kincaid', 'contentsize'];
    }
}