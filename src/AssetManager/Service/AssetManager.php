<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use AssetManager\Service\AssetFilterManagerAwareInterface;
use AssetManager\CacheControl\CacheController;
use AssetManager\Service\AssetFilterManager;
use AssetManager\Exception;
use AssetManager\Resolver\ResolverInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Http\PhpEnvironment\Request;

/**
 * @category    AssetManager
 * @package     AssetManager
 */
class AssetManager implements
    AssetFilterManagerAwareInterface,
    AssetCacheManagerAwareInterface
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var AssetFilterManager The AssetFilterManager service.
     */
    protected $filterManager;

    /**
     * @var AssetCacheManager The AssetCacheManager service.
     */
    protected $cacheManager;

    /**
     * @var CacheController The cache control service
     */
    protected $cacheController;

    /**
     * @var AssetInterface The asset
     */
    protected $asset;

    /**
     * @var string The requested path
     */
    protected $path;

    /**
     * @var array The asset_manager configuration
     */
    protected $config;

    /**
     * Constructor
     *
     * @param ResolverInterface $resolver
     * @param array             $config
     *
     * @return AssetManager
     */
    public function __construct($resolver, $config = array())
    {
        $this->setResolver($resolver);
        $this->setConfig($config);
    }

    /**
     * Set the config
     *
     * @param array $config
     */
    protected function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Check if the request resolves to an asset.
     *
     * @param    RequestInterface $request
     * @return   boolean
     */
    public function resolvesToAsset(RequestInterface $request)
    {
        if (null === $this->asset) {
            $this->asset = $this->resolve($request);
        }

        return (bool)$this->asset;
    }

    /**
     * Cache control service aware interface implementation
     *
     * @param CacheController $cacheController
     */
    public function setCacheController(CacheController $cacheController)
    {
        $this->cacheController = $cacheController;
    }

    /**
     * @return CacheController
     */
    public function getCacheController()
    {
        return $this->cacheController;
    }

    /**
     * Set the resolver to use in the asset manager
     *
     * @param ResolverInterface $resolver
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Get the resolver used by the asset manager
     *
     * @return ResolverInterface
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Set the asset on the response, including headers and content.
     *
     * @param    ResponseInterface $response
     * @return   ResponseInterface
     * @throws   Exception\RuntimeException
     */
    public function setAssetOnResponse(ResponseInterface $response)
    {
        if (!$this->asset instanceof AssetInterface) {
            throw new Exception\RuntimeException(
                'Unable to set asset on response. Request has not been resolved to an asset.'
            );
        }

        // @todo: Create Asset wrapper for mimetypes
        if (empty($this->asset->mimetype)) {
            throw new Exception\RuntimeException('Expected property "mimetype" on asset.');
        }

        $this->getAssetFilterManager()->setFilters($this->path, $this->asset);

        $this->asset    = $this->getAssetCacheManager()->setCache($this->path, $this->asset);
        $mimeType       = $this->asset->mimetype;
        $assetContents  = $this->asset->dump();

        // @codeCoverageIgnoreStart
        if (function_exists('mb_strlen')) {
            $contentLength = mb_strlen($assetContents, '8bit');
        } else {
            $contentLength = strlen($assetContents);
        }
        // @codeCoverageIgnoreEnd

        $headers = $response->getHeaders();

        if (!is_null($this->cacheController)) {
            $this->cacheController->addHeaders($this->asset);
        }

        $headers->addHeaderLine('Content-Transfer-Encoding',   'binary')
                 ->addHeaderLine('Content-Type',                $mimeType)
                 ->addHeaderLine('Content-Length',              $contentLength);

        $response->setContent($assetContents);

        return $response;
    }

    /**
     * Resolve the request to a file.
     *
     * @param RequestInterface $request
     *
     * @return mixed false when not found, AssetInterface when resolved.
     */
    public function resolve(RequestInterface $request)
    {
        if ($this->asset !== null) {

            return $this->asset;
        }

        if (!$request instanceof Request) {

            return false;
        }

        /* @var $request Request */
        /* @var $uri \Zend\Uri\UriInterface */
        $uri        = $request->getUri();
        $fullPath   = $uri->getPath();
        $path       = substr($fullPath, strlen($request->getBasePath()) + 1);
        $this->path = $path;
        $asset      = $this->getResolver()->resolve($path);

        if (!$asset instanceof AssetInterface) {
            return false;
        }

        return $asset;
    }

    /**
     * Set the AssetFilterManager.
     *
     * @param AssetFilterManager $filterManager
     */
    public function setAssetFilterManager(AssetFilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * Get the AssetFilterManager
     *
     * @return AssetFilterManager
     */
    public function getAssetFilterManager()
    {
        return $this->filterManager;
    }

    /**
     * Set the AssetCacheManager.
     *
     * @param AssetCacheManager $filterManager
     */
    public function setAssetCacheManager(AssetCacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Get the AssetCacheManager
     *
     * @return AssetCacheManager
     */
    public function getAssetCacheManager()
    {
        return $this->cacheManager;
    }
}
