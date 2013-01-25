<?php

namespace AssetManager\Service;

class AssetCacheBustingManager
{
    const STRATEGY_NONE = 0;
    const STRATEGY_MODIFICATION = 1;
    const STRATEGY_CONTENT = 2;

    protected $config = array();

    public function __construct($config = array())
    {
        $this->config = $config;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function isEnabled()
    {
        if (isset($this->config['enabled']) && $this->config['enabled']) {
            return true;
        }

        return false;
    }

    public function getOverrideHeadHelper()
    {
        if (isset($this->config['override_head_helper']) && $this->config['override_head_helper']) {
            return true;
        }

        return false;
    }
}
