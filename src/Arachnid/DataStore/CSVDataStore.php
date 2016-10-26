<?php


namespace Arachnid\DataStore;


class CSVDataStore implements DataStore
{

    /**
     * @var resource
     */
    protected $filePointer;

    protected $path;
    protected $knownColumns = [];

    public function __construct($path, $knownColumns)
    {
        $this->path = $path;
        $this->knownColumns = $knownColumns;
    }

    public function init($crawlId)
    {
        $filename = "{$this->path}/{$crawlId}.csv";
        $this->filePointer = fopen($filename, 'w');

        // Write out header
        $headerRow = implode(array_map(function($element) {return '"'.$element.'"';}, $this->knownColumns), ",");
        fwrite($this->filePointer,$headerRow."\n");
    }

    public function writeToStore($url, $data)
    {
        $data['url'] = $url;
        $resultRow = [];

        foreach($this->knownColumns as $column)
        {
            if (array_key_exists($column, $data))
            {
                $item = $data[$column];

                // Quote strings, and encode arrays
                if (is_array($item))
                {
                    $item = json_encode($item);
                }

                if (is_string($item))
                {
                    $item = '"'.$item.'"';
                }

                $resultRow[] = $item;
            } else {
                $resultRow[] = '';
            }
        }

        fwrite($this->filePointer, implode($resultRow, ",")."\n");
    }

    public function close()
    {
        fclose($this->filePointer);
    }

}