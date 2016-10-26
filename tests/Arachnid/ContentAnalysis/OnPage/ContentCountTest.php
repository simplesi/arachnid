<?php


namespace Arachnid\Test;

use Arachnid\ContentAnalysis\OnPage\ContentCounts;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class ContentsCountTest extends TestCase
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