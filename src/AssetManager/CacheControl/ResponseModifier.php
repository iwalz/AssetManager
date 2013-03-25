<?php

namespace AssetManager\CacheControl;

use Assetic\Asset\AssetInterface;
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
     * @var Config
     */
    protected $config = null;

    /**
     * @param Response $response
     */
    public function __construct( Response $response = null)
    {
        $this->response             = $response;
    }

    public function setConfig( Config $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
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

    public function addHeaders(AssetInterface $asset)
    {
        $headers = $this->response->getHeaders();

        $lastModified = date("D,d M Y H:i:s T", $asset->getLastModified());

        $lifetime = $this->config->getLifetime();
        $headers->addHeaderLine('Cache-Control', 'max-age=' . $lifetime .', public');
        $headers->addHeaderLine('Expires', date("D,d M Y H:i:s T", time() + $lifetime));

        $headers->addHeaderLine('Last-Modified', $lastModified);
        $headers->addHeaderLine('Pragma', '');

        #if ($this->hasEtag()) {
        #    $headers->addHeaderLine('ETag', $this->calculateEtag($asset));
        #}
    }
}
