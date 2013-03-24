<?php

namespace AssetManager\CacheControl;

use Zend\Http\Response;

/**
 * Modifies a response
 *
 * @package AssetManager\CacheControl
 */
class ResponseModifier
{
    /**
     * @var Response
     */
    protected $response = null;

    /**
     * @param Response $response
     */
    public function __construct( Response $response = null)
    {
        $this->response             = $response;
    }

    /**
     * @param Response $response
     */
    public function setResponse( Response $response)
    {
        $this->response             = $response;
    }

    /**
     * @return null|Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the Not-Modified header and removes the Cache-Control
     */
    public function enableNotModified()
    {
        $this->response->setStatusCode(304);
        $responseHeaders = $this->response->getHeaders();
        if ( $responseHeaders->has('Cache-Control')) {
            $cacheControlHeader     = $responseHeaders->get('Cache-Control');
            $responseHeaders->removeHeader($cacheControlHeader);
        }
    }
}
