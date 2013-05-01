<?php

namespace AssetManager\CacheBusting;

use AssetManager\CacheControl\CacheController;

/**
 * Manages the CacheBusting behaviour
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetCacheBustingManager
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var CacheController
     */
    protected $cacheController = null;

    /**
     * @param Config $config
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param CacheController $cacheController
     */
    public function setCacheController(CacheController $cacheController)
    {
        $this->cacheController = $cacheController;
    }

    /**
     * @return CacheController|null
     */
    public function getCacheController()
    {
        return $this->cacheController;
    }

    /**
     * Handles specific cache busting use cases
     *
     * @return null|\Zend\Http\Response
     */
    public function handleRequest()
    {
        $requestInspector = $this->cacheController->getRequestInspector();

        if (!$requestInspector->isCacheBustingRequest()) {

            return;
        }

        if ($requestInspector->isIfNoneMatchRequest() || $requestInspector->isIfModifiedSinceRequest()) {
            $responseModifier = $this->cacheController->getResponseModifier();
            $responseModifier->enableNotModified();

            return $responseModifier->getResponse();
        }
        $requestInspector->stripCacheBustingTag();

        $responseModifier = $this->cacheController->getResponseModifier();
        #$responseModifier->setCache(null);
    }
}
