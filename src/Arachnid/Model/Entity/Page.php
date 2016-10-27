<?php


namespace Arachnid\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Page
 * @package Arachnid\Model\Entity
 *
 * @ORM\Table(name="pages")
 * @ORM\Entity
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
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @ORM\Column(type="integer")
     */
    protected $statusCode;

    /**
     * @ORM\OneToMany(targetEntity="Metric", mappedBy="page")
     */
    protected $metrics;



}