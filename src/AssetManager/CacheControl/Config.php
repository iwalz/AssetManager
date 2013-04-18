<?php

namespace AssetManager\CacheControl;

use AssetManager\Config\AbstractConfig;
use AssetManager\Exception\InvalidArgumentException;

class Config extends AbstractConfig
{
    public function getConfigKey()
    {
        return 'cache_control';
    }

    public function getLifetime()
    {
        $config = $this->getConfig();
        $lifetime = $config['lifetime'];

        if (is_numeric($lifetime)) {

            return $lifetime;
        }

        $minute = 60;
        $hour = 60*60;
        $day = 24*60*60;

        preg_match("/(\d+)(\w)/", $lifetime, $match);

        if ($match[2] == 'd') {

            return $match[1]*$day;
        }

        if ($match[2] == 'h') {

            return $match[1]*$hour;
        }

        if ($match[2] == 'm') {

            return $match[1]*$minute;
        }

        throw new InvalidArgumentException("Valid formatters are d,h,m");
    }

    public function isEnabled()
    {
        $this->enableGlobalConfig(true);
        $config = $this->getConfig(false);
        $this->enableGlobalConfig(false);

        return (bool)$config['enabled'];
    }

    public function getRequiredKeys()
    {
        return array('lifetime');
    }

    public function getStrategy()
    {
        $config = $this->getConfig();

        return isset($config['strategy']) ? $config['strategy'] : false;
    }

    public function getStatic()
    {
        $config = $this->getConfig();

        return $config['static'];
    }
}
