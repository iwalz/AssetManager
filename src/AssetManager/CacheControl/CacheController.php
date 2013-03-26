<?php

namespace AssetManager\CacheControl;

use Assetic\Asset\AssetInterface;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * Cache controller service
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class CacheController
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var RequestInspector
     */
    protected $requestInspector = null;

    /**
     * @var ResponseModifier
     */
    protected $responseModifier = null;

    /**
     * @param Config $config
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config;
    }

    /**
     * Handles the response based on the request and the validation
     *
     * @param AssetInterface $asset
     * @return null|Response
     */
    public function handleRequest(AssetInterface $asset)
    {
        $this->config->setAsset($asset);

        if (
            $this->requestInspector->isCacheBustingRequest()
            && $this->requestInspector->isIfNoneMatchRequest()
        ) {
            $this->responseModifier->enableNotModified();

            return $this->responseModifier->getResponse();
        }

        if ($this->requestInspector->isCacheBustingRequest()) {
            $this->requestInspector->stripCacheBustingTag();
        }


        if ($this->requestInspector->isIfModifiedSinceRequest()) {
            $lastModified = $asset->getLastModified();
            $modifiedSince = $this->requestInspector->getModifiedSince();

            if ($lastModified <= $modifiedSince) {
                $this->responseModifier->enableNotModified();

                return $this->responseModifier->getResponse();
            }
        }

        /*if ($headers->has('If-None-Match')) {
            $cacheController = $assetManager->getCacheController();
            $asset = $assetManager->resolve($request);

            $assetManager->getAssetFilterManager()->setFilters($uri, $asset);
            $etag = $cacheController->calculateEtag($asset);

            $match = $headers->get('If-None-Match')->getFieldValue();

            if ($etag == $match) {
                $response->setStatusCode(304);
                $responseHeaders = $response->getHeaders();
                $responseHeaders->addHeaderLine('Cache-Control', '');
                return $response;
            }
        }*/
    }

    /**
     * Add cache control headers to the response
     *
     * @param \Zend\Http\Headers $headers
     */
    public function addHeaders(AssetInterface $asset)
    {
        $this->responseModifier->addHeaders($asset);
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        if ($this->responseModifier !== null) {
            $this->responseModifier->setConfig($config);
        }
    }

    /**
     * @return Config|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param RequestInspector $requestInspector
     */
    public function setRequestInspector(RequestInspector $requestInspector)
    {
        $this->requestInspector = $requestInspector;
    }

    /**
     * @return RequestInspector|null
     */
    public function getRequestInspector()
    {
        return $this->requestInspector;
    }

    /**
     * @param Request $request
     */
    public function setRequest( Request $request)
    {
        $this->requestInspector->setRequest($request);

        $this->config->setPath($request);
    }

    /**
     * @param ResponseModifier $responseModifier
     */
    public function setResponseModifier(ResponseModifier $responseModifier)
    {
        $this->responseModifier = $responseModifier;
    }

    /**
     * @return ResponseModifier|null
     */
    public function getResponseModifier()
    {
        return $this->responseModifier;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->responseModifier->setResponse($response);
    }
}
