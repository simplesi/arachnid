<?php


namespace Arachnid\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Page
 * @package Arachnid\Model\Entity
 *
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="PageRepository")
 */
class Page
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $url;

    /**
     * @ORM\OneToMany(targetEntity="Metric", mappedBy="page")
     */
    protected $metrics;

    /**
     * Cache of metricName => metric to ensure uniqueness
     * @var array
     */
    protected $metricMap = [];


    /**
     * @ORM\OneToMany(targetEntity="Metadata", mappedBy="page")
     */
    protected $metadata;

    /**
     * Cache of metricName => metric to ensure uniqueness
     * @var array
     */
    protected $metadataMap = [];


    public function __construct($url)
    {
        $this->url = $url;
        $this->metrics = new ArrayCollection();
        $this->metadata = new ArrayCollection();
    }

    /**
     * @param $name
     * @param $value
     */
    protected function addMetric($name, $value, EntityManager $em)
    {
        if (count($this->metrics) != count($this->metricMap))
        {
            $this->buildMetricMap();
        }

        // First remove any existing metrics with this name
        if (array_key_exists($name,$this->metricMap)){
            $toRemove = $this->metricMap[$name];
            $this->metrics->removeElement($toRemove);
            $em->remove($toRemove);
        }

        $toAdd = new Metric($name, $value, $this);
        $em->persist($toAdd);
        $this->metrics->add($toAdd);
        $this->metricMap[$name] = $toAdd;
    }

    /**
     * @param $data
     */
    public function addMetrics($data, EntityManager $em)
    {
        foreach($data as $name => $value)
        {
            $this->addMetric($name, $value, $em);
        }
    }

    protected function buildMetricMap()
    {
        foreach($this->metrics as $metric)
        {
            $this->metricMap[$metric->getType()] = $metric;
        }
    }

    /**
     * @param $name
     * @param $value
     */
    protected function addMetadata($name, $value, EntityManager $em)
    {
        if (count($this->metadata) != count($this->metadataMap))
        {
            $this->buildMetadataMap();
        }

        // First remove any existing metrics with this name
        if (array_key_exists($name,$this->metadataMap)){
            $toRemove = $this->metadataMap[$name];
            $this->metadata->removeElement($toRemove);
            $em->remove($toRemove);
        }

        $toAdd = new Metadata($name, $value, $this);
        $em->persist($toAdd);
        $this->metadata->add($toAdd);
        $this->metadataMap[$name] = $toAdd;
    }

    /**
     * @param $data
     */
    public function addMetadatas($data, EntityManager $em)
    {
        foreach($data as $name => $value)
        {
            $this->addMetadata($name, $value, $em);
        }
    }

    protected function buildMetadataMap()
    {
        foreach($this->metadata as $metadata)
        {
            $this->metadataMap[$metadata->getType()] = $metadata;
        }
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

}