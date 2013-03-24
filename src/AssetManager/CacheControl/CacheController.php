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
     * @var array
     */
    protected $config = array();

    /**
     * @var RequestInspector
     */
    protected $requestInspector = null;

    /**
     * @var ResponseModifier
     */
    protected $responseModifier = null;

    public function __construct($config = array())
    {
        if (isset($config['cache_control'])) {
            $this->setConfig($config['cache_control']);
        }
    }

    /**
     * Handles the response based on the request and the validation
     *
     * @param AssetInterface $asset
     * @return null|Response
     */
    public function handleRequest(AssetInterface $asset)
    {
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
            $modifiedSince = strtotime($this->requestInspector->getRequest()->getHeaders()->get('If-Modified-Since')->getDate());

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
    public function addHeaders(Headers $headers, AssetInterface $asset)
    {
        if ($this->config !== array()) {
            $lastModified = date("D,d M Y H:i:s T", $asset->getLastModified());

            $lifetime = $this->getLifetime();
            $headers->addHeaderLine('Cache-Control', 'max-age=' . $lifetime .', public');
            $headers->addHeaderLine('Expires', date("D,d M Y H:i:s T", time() + $lifetime));

            $headers->addHeaderLine('Last-Modified', $lastModified);
            $headers->addHeaderLine('Pragma', '');

            if ($this->hasEtag()) {
                $headers->addHeaderLine('ETag', $this->calculateEtag($asset));
            }
        }
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return bool|null
     */
    public function hasEtag()
    {
        if (isset($this->config['etag'])) {
            return $this->config['etag'];
        }

        return false;
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        if (isset($this->config['lifetime'])) {
            $lifetime = $this->config['lifetime'];
        }

        $minute = 60;
        $hour = 60*60;
        $day = 24*60*60;

        preg_match("/(\d+)(\w)/", $lifetime, $match);

        if (count($match) === 0 || count($match) < 3) {
            throw new \AssetManager\Exception\InvalidArgumentException("Invalid format");
        }

        if ($match[2] == 'd') {
            return $match[1]*$day;
        } elseif ($match[2] == 'h') {
            return $match[1]*$hour;
        } elseif ($match[2] == 'm') {
            return $match[1]*$minute;
        } else {
            throw new \AssetManager\Exception\InvalidArgumentException("Valid formatters are d,h,m");
        }
    }

    /**
     * @param AssetInterface $asset
     * @return string
     */
    public function calculateEtag(AssetInterface $asset)
    {
        $mtime = $asset->getLastModified();
        $size = null;

        $assetContents = $asset->dump();

        // @codeCoverageIgnoreStart
        if (function_exists('mb_strlen')) {
            $size = mb_strlen($assetContents, '8bit');
        } else {
            $size = strlen($assetContents);
        }
        // @codeCoverageIgnoreEnd

        $etag = sprintf('%x-%x-%016x', 1, $size, $mtime);

        return $etag;
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
