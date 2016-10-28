<?php


namespace Arachnid\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Metadata
 * @package Arachnid\Model\Entity
 *
 * @ORM\Table(name="metadata")
 * @ORM\Entity
 */
class Metadata
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
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="metadata")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $page;

    /**
     * @ORM\Column(type="string", length=1024)
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
     * @return string
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