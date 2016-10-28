<?php


namespace Arachnid\Client;

use Goutte\Client;

/**
 * We need a record of which redirects we used, so we just wrap the Goutte client to do that
 *
 * Class RecordingClient
 * @package Arachnid\Client
 */
class RecordingClient extends Client
{
    protected $redirectList = [];

    public function followRedirect()
    {
        $this->redirectList[] = [$this->internalResponse->getStatus(), $this->request->getUri()];
        return parent::followRedirect();
    }

    /**
     * Get the set of [http status, urls] we redirected through
     * @return array
     */
    public function getRedirectList()
    {
        return $this->redirectList;
    }

}