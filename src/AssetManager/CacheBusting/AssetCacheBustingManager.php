<?php

namespace AssetManager\CacheBusting;

class AssetCacheBustingManager
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @param Config $config
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
