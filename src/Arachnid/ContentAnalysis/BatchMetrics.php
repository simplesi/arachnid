<?php


namespace Arachnid\ContentAnalysis;


interface BatchMetrics
{
    /**
     * @return BatchResults
     */
    public function getResults();
}