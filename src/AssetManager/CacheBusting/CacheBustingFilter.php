<?php

namespace AssetManager\CacheBusting;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\BaseCssFilter;
use Assetic\Cache\CacheInterface;

class CacheBustingFilter extends BaseCssFilter
{
    /**
     * @var CacheInterval
     */
    protected $cache = null;

    /**
     * Filters an asset after it has been loaded.
     *
     * @param AssetInterface $asset An asset
     */
    public function filterLoad(AssetInterface $asset)
    { }

    /**
     * Filters an asset just before it's dumped.
     *
     * @param AssetInterface $asset An asset
     */
    public function filterDump(AssetInterface $asset)
    {
        $cache = $this->cache;

        $callback = function($match) use ($cache) {
            $fileName = substr($match[2], strrpos($match[2], '/')+1);

            if ($cache->has($fileName . '_etag')) {

                return str_replace($fileName, $fileName . ";AM" . $cache->get($fileName . '_etag'), $match[0]);
            } else {

                return $match[0];
            }
        };
        $asset->setContent($this->filterUrls($asset->getContent(), $callback));
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return CacheInterval|null
     */
    public function getCache()
    {
        return $this->cache;
    }
}
