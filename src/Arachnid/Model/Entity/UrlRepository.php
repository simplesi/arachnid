<?php


namespace Arachnid\Model\Entity;


use Doctrine\ORM\EntityRepository;

class UrlRepository extends EntityRepository
{
    /**
     * @param $url
     * @return Url
     */
    public function findOrCreateOne($url, $statusCode = 0)
    {
        $urlObject = $this->findOneBy(['url' => $url]);


        if ($urlObject === null)
        {
            $urlObject = new Url($url, $statusCode);
            $this->_em->persist($urlObject);

        } else {
            // Ensure the status code is up to date if we got one
            if ($statusCode != 0 && $urlObject->getStatusCode() != $statusCode)
            {
                $urlObject->setStatusCode($statusCode);
            }
        }
        return $urlObject;
    }

}