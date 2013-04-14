<?php

namespace AssetManagerTest\CacheBusting;

use AssetManager\CacheBusting\Config;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testEnabled()
    {
        $config = array(
            'asset_manager' => array(
                'cache_busting' => array(
                    'enabled' => true
                )
            )
        );
        $cacheBustingConfig = new Config($config);

        $this->assertTrue($cacheBustingConfig->isEnabled());

        $config = array(
            'asset_manager' => array(
                'cache_busting' => array(
                    'enabled' => false
                )
            )
        );
        $cacheBustingConfig->setConfig($config);

        $this->assertFalse($cacheBustingConfig->isEnabled());
    }

    public function testDefaultCache()
    {
        $config = array(
            'asset_manager' => array(
                'cache_busting' => array(
                    'enabled' => true
                )
            )
        );
        $cacheBustingConfig = new Config($config);

        $this->assertFalse($cacheBustingConfig->getCache());
    }
}
