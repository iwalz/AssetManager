<?php

namespace AssetManager\CacheBusting;

use AssetManager\Config\AbstractConfig;

/**
 * Cache Busting config implementation
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class Config extends AbstractConfig
{
    /**
     * {@inheritDoc}
     */
    public function getRequiredKeys()
    {
        return array('enabled');
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigKey()
    {
        return 'cache_busting';
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $this->enableGlobalConfig(true);
        $config = $this->getConfig(false);
        $this->enableGlobalConfig(false);

        return (bool)$config['enabled'];
    }

    /**
     * @return bool|string
     */
    public function getCache()
    {
        $this->enableGlobalConfig(true);
        $config = $this->getConfig();
        $this->enableGlobalConfig(false);

        if (!isset($config['cache'])) {

            return false;
        }

        return $config['cache'];
    }

    /**
     * Get the cache lifetime
     *
     * @return int
     */
    public function getValidationLifetime()
    {
        $config = $this->getConfig();

        return $config['validation_lifetime'];
    }
}
