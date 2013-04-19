<?php

namespace AssetManager\CacheControl;


use Assetic\Asset\AssetInterface;
use Assetic\Cache\CacheInterface;
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
     * @var CacheInterface
     */
    protected $cache = null;

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


    public function addHeaders(AssetInterface $asset)
    {
        $headers = $this->response->getHeaders();
        $strategy = $this->config->getStrategy();

        $lastModified = date("D,d M Y H:i:s T", $asset->getLastModified());
        $lifetime = $this->config->getLifetime();

        $headers->addHeaderLine('Cache-Control', 'max-age=' . $lifetime . ', public');
        $headers->addHeaderLine('Expires', date("D,d M Y H:i:s T", time() + $lifetime));
        $headers->addHeaderLine('Last-Modified', $lastModified);
        $headers->addHeaderLine('Pragma', '');

        $this->checksumHandler->setAsset($asset);
        if ($strategy) {
            $this->checksumHandler->setStrategy($strategy);
            $this->checksumHandler->setConfig($this->config);
        }
        $etag = $this->checksumHandler->getChecksum();
        $headers->addHeaderLine('ETag', $etag);

        if (!is_null($this->cache)) {
            $this->cache->ttl = $this->config->getValidationLifetime();
            $this->cache->set($asset->getSourcePath() . '_etag', $etag);
            $this->cache->set($asset->getSourcePath() . '_lastmodified', $lastModified);
        }
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    public function getCache()
    {
        return $this->cache;
    }

}
