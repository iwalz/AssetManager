<?php

namespace AssetManager\CacheBusting;

use AssetManager\CacheControl\Config as CacheControlConfig;

/**
 * The CacheController config implementation for Cache Busting
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class CacheControllerConfig extends CacheControlConfig
{
    /**
     * {@inheritDoc}
     */
    public function getConfigKey()
    {
        return 'cache_busting';
    }

    /**
     * Get the lifetime in seconds for cache busting expiration
     *
     * @return int|string
     */
    public function getLifetime()
    {
        $this->enableGlobalConfig(true);
        $config = $this->getConfig();
        $this->enableGlobalConfig(false);

        return $config['lifetime'];
    }
}
