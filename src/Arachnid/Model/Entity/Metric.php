<?php


namespace Arachnid\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Metric
 * @package Arachnid\Model\Entity
 *
 * @ORM\Table(name="metrics")
 * @ORM\Entity
 */
class Metric
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @var Page
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="metrics")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $page;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\Column(type="date")
     */
    private $lastUpdated;

    public function __construct($type, $value, Page $page)
    {
        $this->type = $type;
        $this->value = $value;
        $this->page = $page;
        $this->lastUpdated = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

}