<?php

namespace AssetManagerTest\CacheBusting;

use AssetManager\CacheBusting\CacheControllerConfig;
use PHPUnit_Framework_TestCase;

class CacheControllerConfigTest extends PHPUnit_Framework_TestCase
{
    public function testCacheControllerConfig()
    {
        $config = array(
            'asset_manager' => array(
                'cache_busting' => array(
                    'lifetime' => 60
                )
            )
        );

        $cacheControllerConfig = new CacheControllerConfig($config);
        $this->assertSame(60, $cacheControllerConfig->getLifetime());
        $this->assertEquals('cache_busting', $cacheControllerConfig->getConfigKey());
    }
}
