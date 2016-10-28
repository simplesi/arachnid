<?php


namespace Arachnid\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Url
 * @package Arachnid\Model\Entity
 *
 * @ORM\Table(name="urls")
 * @ORM\Entity(repositoryClass="UrlRepository")
 */
class Url
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
     * @var Url
     * @ORM\Column(type="string", length=255)
     */
    protected $url;

    /**
     * @var Page
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="allUrls")
     */
    protected $page;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $statusCode;

    /**
     * @var Url
     * @ORM\ManyToOne(targetEntity="Url")
     */
    protected $redirectsTo;


    public function __construct($url, $statusCode)
    {
        $this->url = $url;
        $this->statusCode = $statusCode;
    }

    public function addRedirectTo(Url $url)
    {
        $this->redirectsTo = $url;
    }

    public function addPage(Page $page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($code)
    {
        $this->statusCode = $code;
    }

    public function getUrl()
    {
        return $this->url;
    }

}