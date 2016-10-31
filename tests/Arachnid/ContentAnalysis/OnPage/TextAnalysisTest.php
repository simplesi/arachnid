<?php


namespace Arachnid\Test\ContentAnalysis\OnPage;

use Arachnid\Test\BaseTestCase;
use Arachnid\ContentAnalysis\OnPage\TextAnalysis;
use Symfony\Component\DomCrawler\Crawler;

class TextAnalysisTest extends \PHPUnit_Framework_TestCase
{
    public function testExtract()
    {
        $testClass = new TextAnalysis();
        $results = $testClass->analyse($this->getCrawler('sampleDrupalPage.html'),'testUrl');

        $this->assertEquals(72.9, $results['flesch-kincaid']);
        $this->assertEquals(797, $results['wordcount']);
        $this->assertEquals(51, $results['sentencecount']);
        $this->assertEquals(137830, $results['contentsize']);


        $results = $testClass->analyse($this->getCrawler('sampleOldPressRelease.html'),'testUrl');

        $this->assertEquals(74.6, $results['flesch-kincaid']);
        $this->assertEquals(316, $results['wordcount']);
        $this->assertEquals(24, $results['sentencecount']);
        $this->assertEquals(12698, $results['contentsize']);

    }

    protected function getCrawler($filename)
    {
        return new Crawler(file_get_contents( __DIR__ . '/../../../fixture/'.$filename));
    }

}