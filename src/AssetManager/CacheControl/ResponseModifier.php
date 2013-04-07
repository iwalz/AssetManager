<?php

namespace AssetManager\CacheControl;


use Assetic\Asset\AssetInterface;
use Zend\Http\Response;
use AssetManager\Checksum\ChecksumHandler;

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
     * @var ChecksumHandler
     */
    protected $checksumHandler = null;

    /**
     * @param Response $response
     */
    public function __construct(Response $response = null)
    {
        $this->response = $response;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ChecksumHandler $checksumHandler
     */
    public function setChecksumHandler(ChecksumHandler $checksumHandler)
    {
        $this->checksumHandler = $checksumHandler;
    }


    /**
     * @return ChecksumHandler|null
     */
    public function getChecksumHandler()
    {
        return $this->checksumHandler;
    }


    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
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

        if ($responseHeaders->has('Cache-Control')) {
            $cacheControlHeader = $responseHeaders->get('Cache-Control');
            $responseHeaders->removeHeader($cacheControlHeader);
        }
    }


    public function addHeaders(AssetInterface $asset, $strategy = 'etag')
    {
        $headers = $this->response->getHeaders();

        $lastModified = date("D,d M Y H:i:s T", $asset->getLastModified());
        $lifetime = $this->config->getLifetime();

        $headers->addHeaderLine('Cache-Control', 'max-age=' . $lifetime . ', public');
        $headers->addHeaderLine('Expires', date("D,d M Y H:i:s T", time() + $lifetime));
        $headers->addHeaderLine('Last-Modified', $lastModified);
        $headers->addHeaderLine('Pragma', '');

        $this->checksumHandler->setAsset($asset);
        $this->checksumHandler->setStrategy($strategy);
        $headers->addHeaderLine('ETag', $this->checksumHandler->getChecksum());
    }

}
