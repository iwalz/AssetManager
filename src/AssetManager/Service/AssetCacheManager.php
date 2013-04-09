<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCache;
use Assetic\Cache\CacheInterface;
use Assetic\Cache;
use AssetManager\Cache\FilePathCache;
use AssetManager\Exception;

class AssetCacheManager
{
    /**
     * @var array Cache configuration.
     */
    protected $config;

    /**
     * @var Assetic\Cache\CacheInterface
     */
    protected $cache    = null;

    /**
     * Construct the AssetCacheManager
     *
     * @param   array $config
     * @return  AssetCacheManager
     */
    public function __construct(array $config = array())
    {
        $this->setConfig($config);
    }

    /**
     * Get the cache configuration.
     *
     * @return  array
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the cache configuration.
     *
     * @param array $config
     */
    protected function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Returns and initializes the cache
     *
     * @param string $path
     * @param AssetInterface $asset
     * @return Assetic\Cache\CacheInterface
     */
    public function getCacheInstance($path = null, AssetInterface $asset = null)
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        if ($path === null || $asset === null) {
            throw new Exception\InvalidArgumentException('You need to set the path and the asset to detect the cache settings');
        }

        $caching = null;
        $config  = $this->getConfig();

        if (!empty($config[$path])) {
            $caching = $config[$path];
        } elseif (!empty($config['default'])) {
            $caching = $config['default'];
        }

        if (null === $caching) {
            return $asset;
        }

        if (empty($caching['cache'])) {
            return $asset;
        }

        $cacher = null;

        if (is_callable($caching['cache'])) {
            $cacher = $caching['cache']($path);
        } else {
            // @codeCoverageIgnoreStart
            $factories  = array(
                'FilesystemCache' => function($options) {
                    if (empty($options['dir'])) {
                        throw new Exception\RuntimeException(
                            'FilesystemCache expected dir entry.'
                        );
                    }
                    $dir = $options['dir'];
                    return new Cache\FilesystemCache($dir);
                },
                'ApcCache' => function($options) {
                    return new Cache\ApcCache();
                },
                'FilePathCache' => function($options) use ($path) {
                    if (empty($options['dir'])) {
                        throw new Exception\RuntimeException(
                            'FilesystemCache expected dir entry.'
                        );
                    }
                    $dir = $options['dir'];
                    return new FilePathCache($dir, $path);
                }
            );
            // @codeCoverageIgnoreEnd

            $type  = $caching['cache'];
            $type .= (substr($type, -5) === 'Cache') ? '' : 'Cache';

            if (!isset($factories[$type])) {
                return $asset;
            }

            $options = empty($caching['options']) ? array() : $caching['options'];
            $cacher  = $factories[$type]($options);
        }

        if (!$cacher instanceof CacheInterface) {
            return $asset;
        }
        $this->cache = $cacher;

        return $cacher;
    }

    /**
     * Set the cache (if any) on the asset, and return the new AssetCache.
     *
     * @param   string$path
     * @param   AssetInterface $asset
     *
     * @return  AssetCache
     */
    public function setCache($path, AssetInterface $asset)
    {
        $cacher = $this->getCacheInstance($path, $asset);

        if (!$cacher instanceof CacheInterface) {
            return $asset;
        }

        $assetCache             = new AssetCache($asset, $cacher);
        $assetCache->mimetype   = $asset->mimetype;

        return $assetCache;
    }
}
