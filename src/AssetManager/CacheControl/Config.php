<?php

namespace AssetManager\CacheControl;

use AssetManager\Config\AbstractConfig;
use AssetManager\Exception\InvalidArgumentException;

/**
 * Implements the CacheControl configuration
 *
 * @package AssetManager\CacheControl
 */
class Config extends AbstractConfig
{
    /**
     * {@inheritDoc}
     */
    public function getConfigKey()
    {
        return 'cache_control';
    }

    /**
     * Get the lifetime, can handle d (day), h (hour) and m (minute)
     *
     * @return int|string
     * @throws \AssetManager\Exception\InvalidArgumentException
     */
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

    /**
     * Check if this feature is enabled (on the root level)
     *
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
     * {@inheritDoc}
     */
    public function getRequiredKeys()
    {
        return array('lifetime');
    }

    /**
     * Get the used strategy
     *
     * @return bool|string
     */
    public function getStrategy()
    {
        $config = $this->getConfig();

        return isset($config['strategy']) ? $config['strategy'] : false;
    }

    /**
     * Get the static value
     *
     * @return string
     */
    public function getStatic()
    {
        $config = $this->getConfig();

        return $config['static'];
    }
}
