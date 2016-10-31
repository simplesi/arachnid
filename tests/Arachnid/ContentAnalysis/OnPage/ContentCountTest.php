<?php


namespace Arachnid\Test\ContentAnalysis\OnPage;

use Arachnid\Test\BaseTestCase;
use Arachnid\ContentAnalysis\OnPage\ContentCounts;
use Symfony\Component\DomCrawler\Crawler;


class ContentsCountTest extends \PHPUnit_Framework_TestCase
{
    public function testExtract()
    {
        $testClass = new ContentCounts();
        $results = $testClass->analyse($this->getCrawler('sampleDrupalPage.html'),'testUrl');
        $this->assertEquals(6, $results['count.image']);

        $results = $testClass->analyse($this->getCrawler('video.html'),'testUrl');
        $this->assertEquals(1, $results['count.video']);
    }

    protected function getCrawler($filename)
    {
        return new Crawler(file_get_contents( __DIR__ . '/../../../fixture/'.$filename));
    }

}