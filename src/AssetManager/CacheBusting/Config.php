<?php

namespace AssetManager\CacheBusting;

use AssetManager\Config\AbstractConfig;

class Config extends AbstractConfig
{
    public function getRequiredKeys()
    {
        return array('enabled');
    }

    public function getConfigKey()
    {
        return 'cache_busting';
    }

    public function isEnabled()
    {
        $this->enableGlobalConfig(true);
        $config = $this->getConfig(false);
        $this->enableGlobalConfig(false);

        return (bool)$config['enabled'];
    }
}
