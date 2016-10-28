<?php


namespace Arachnid\Model\Entity;


use Doctrine\ORM\EntityRepository;

class PageRepository extends EntityRepository
{
    /**
     * @param $url
     * @return Page|null
     */
    public function findOrCreateOneByUrl(Url $url)
    {
        $page = $this->findOneBy(['url' => $url]);

        if ($page === null)
        {
            $page = new Page($url);
            $this->_em->persist($page);
        }

        return $page;
    }

}