<?php


namespace Arachnid\Test;

use Arachnid\ContentAnalysis\OnPage\ExtractHtmlMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class ExtractHtmlMetadataTest extends TestCase
{
    public function testExtract()
    {
        $testClass = new ExtractHtmlMetadata();


        $results = $testClass->analyse($this->getCrawler('sampleDrupalPage.html'),'testUrl');

        $this->assertEquals('Barclays: Don’t frack my home | Friends of the Earth', $results['title']);
        $this->assertEquals('Drupal 7 (http://drupal.org)', $results['generator']);
        $this->assertEquals('anna.baum', $results['creator']);
        $this->assertEquals('2016-10-21', $results['date']);

        $results = $testClass->analyse($this->getCrawler('sampleOldPressRelease.html'),'testUrl');

        $this->assertEquals('Friends of the Earth: Press Release: MOST LANDFILLS FAILING TO MEET STANDARDS', $results['title']);
        $this->assertEquals('Dreamweaver', $results['generator']);
        $this->assertEquals('2002-12-20', $results['date']);
    }

    protected function getCrawler($filename)
    {
        return new Crawler(file_get_contents( __DIR__ . '/../../../fixture/'.$filename));
    }

}