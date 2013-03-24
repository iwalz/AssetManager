<?php

namespace AssetManager\CacheControl;

use Zend\Http\Request;

/**
 * Inspects a request and provide information about the request behaviour
 *
 * @package AssetManager\CacheControl
 */
class RequestInspector
{
    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @param Request $request
     */
    public function __construct( Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @param Request $request
     */
    public function setRequest( Request $request )
    {
        $this->request = $request;
    }

    /**
     * @return null|Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns true if the current request is a cachebusting request,
     * identified by ;AM in the URL
     *
     * @return bool
     */
    public function isCacheBustingRequest()
    {
        $uri            = $this->request->getUri();
        $pos            = strpos($uri->getPath(), ';AM');

        if ($pos !== false) {
            return true;
        }

        return false;
    }

    /**
     * Identifies a 'If-None-Match' request by the presence of the header
     *
     * @return bool
     */
    public function isIfNoneMatchRequest()
    {
        /** @var $headers  \Zend\Http\Headers */
        $headers        = $this->request->getHeaders();

        return $headers->has('If-None-Match');
    }

    /**
     * Identifies a 'If-Modified-Since' request by the presence of the header
     *
     * @return bool
     */
    public function isIfModifiedSinceRequest()
    {
        /** @var $headers  \Zend\Http\Headers */
        $headers        = $this->request->getHeaders();

        return $headers->has('If-Modified-Since');
    }

    /**
     * Removes the ;AM part of the URI, to make routing compatible
     * with asset resolution
     */
    public function stripCacheBustingTag()
    {
        $uri            = $this->request->getUri();
        $pos            = strpos($uri->getPath(), ';AM');

        $uri->setPath(substr($uri->getPath(), 0, $pos));
    }
}
