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
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="metrics")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $page;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $value;

    private $lastUpdated;


}