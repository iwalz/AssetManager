<?php

namespace AssetManager\CacheBusting;

class AssetCacheBustingManager
{
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
