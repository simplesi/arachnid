<?php


namespace Arachnid\ContentAnalysis\OnPage;


use Symfony\Component\DomCrawler\Crawler;

class ExtractHtmlMetadata extends ContentAnalyser
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
        $content->filterXPath('//head//meta')->each(function (Crawler $node) use (&$data) {
            switch ($node->attr('name')) {
                case 'generator':
                    $data['generator'] = trim($node->attr('content'));
                    break;
                case 'dcterms.creator':
                    $data['creator'] = trim($node->attr('content'));
                    break;
                case 'dcterms.date':
                    $data['date'] = $this->parseDate(trim($node->attr('content')));
                    break;
                case 'description':
                    $data['description'] = trim($node->attr('content'));
                    $data['description.length'] = strlen(trim($node->attr('content')));
                    break;
                case 'RELEASE_DATE':
                    $data['date'] = $this->parseDate(trim($node->attr('content')), 'd M Y');
                    break;
            };

        });

        $content->filterXPath('//head//link')->each(function (Crawler $node) use (&$data) {
            switch ($node->attr('rel')) {
                case 'canonical':
                    $data['url.canonical'] = $node->attr('href');
                    break;
                case 'shortlink':
                    // Pull the node id out of the shortlink, if available
                    $link = $node->attr('href');
                    if (strpos($link, 'https://www.foe.co.uk/node/') !== false) {
                        $data['node_id'] = substr($link, 27);
                    }
                    break;
            }
        });

        $html = $content->html();

        if (!isset($data['generator'])) {
            // Work out if it's actually a dreamweaver page
            if (strpos($html, '#BeginTemplate') !== false) {
                $data['generator'] = 'Dreamweaver';
            }
        }

        if (!isset($data['date'])) {
            // Try to work out the date from the html

        }


        // TITLE
        $content->filterXPath('//head//title')->each(function (Crawler $node) use ($url, &$data) {
            $data['title'] = preg_replace('/\s+/', ' ',trim($node->text()));
        });

        // H1s
        $h1_count = $content->filter('h1')->count();
        $data['h1_count'] = $h1_count;

        return $data;
    }

    private function parseDate($string, $format = null){
        try {
            if ($format === null) {
                $date = new \DateTime($string);
            } else {
                $date = \DateTime::createFromFormat($format, $string);
            }
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        } catch (\Exception $e){
        }
        return '';
    }

    /**
     * Return an array of the keys that might be returned in the result set (eg for csv columns)
     * @return array
     */
    public function getResultKeys()
    {
        return [
            'generator',
            'title',
            'description',
            'h1_count',
            'date',
            'creator',
            'description.length'
        ];
    }
}