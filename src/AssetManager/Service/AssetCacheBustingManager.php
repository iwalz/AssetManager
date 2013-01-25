<?php

namespace AssetManager\Service;

class AssetCacheBustingManager
{
    /**
     * No hash
     */
    const STRATEGY_NONE         = 0;
    /**
     * Hash based on last modified timestamp
     */
    const STRATEGY_MODIFICATION = 1;
    /**
     * Hash based on the content checksum
     */
    const STRATEGY_CONTENT      = 2;
    /**
     * Hash based on a static value (e.g. a version number)
     */
    const STRATEGY_STATIC       = 4;
    /**
     * Random hash (for cache-versioning)
     */
    const STRATEGY_RANDOM       = 8;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->config = $config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if (isset($this->config['enabled']) && $this->config['enabled']) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getOverrideHeadHelper()
    {
        if (isset($this->config['override_head_helper']) && $this->config['override_head_helper']) {
            return true;
        }

        return false;
    }
}
