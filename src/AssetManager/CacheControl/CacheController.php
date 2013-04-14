<?php

namespace AssetManager\CacheControl;

use AssetManager\Checksum\ChecksumHandler;
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
     * @var ChecksumHandler
     */
    protected $checksumHandler = null;

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
    public function handleRequest(AssetInterface $asset, $strategy = 'etag')
    {
        $this->config->setAsset($asset);

        // Handle Modified-Since requests
        if ($this->requestInspector->isIfModifiedSinceRequest()) {
            $lastModified = $asset->getLastModified();
            $modifiedSince = $this->requestInspector->getModifiedSince();

            if (!is_null($this->responseModifier->getCache())) {
                $this->addHeaders($asset);
            }

            if ($lastModified <= $modifiedSince) {
                $this->responseModifier->enableNotModified();

                return $this->responseModifier->getResponse();
            }
        }

        // Handle None-Match requests, only if Modified-Since is not available
        if ($this->requestInspector->isIfNoneMatchRequest()) {

            $checksumHandler = $this->responseModifier->getChecksumHandler();
            $checksumHandler->setAsset($asset);
            $checksumHandler->setStrategy($strategy);
            $checksumHandler->setPath($this->config->getPath());

            if (!is_null($this->responseModifier->getCache())) {
                $this->addHeaders($asset);
            }

            if ($this->requestInspector->getIfNoneMatch() == $checksumHandler->getChecksum()) {
                $this->responseModifier->enableNotModified();

                return $this->responseModifier->getResponse();
            }
        }
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
