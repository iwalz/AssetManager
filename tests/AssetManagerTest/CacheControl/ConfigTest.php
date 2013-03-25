<?php

namespace AssetManagerTest\CacheControl;

use AssetManager\CacheControl\Config;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    protected $config = null;

    public function setUp()
    {
        $this->config = array(
            'asset_manager' => array(
                'cache_control' => array(
                    'lifetime' => '5m'
                )
            )
        );
    }

    public function testGetLifetimme()
    {
        $config = new Config();
        $config->setConfig($this->config);
        $this->assertSame(300, $config->getLifetime());

        $this->config['asset_manager']['cache_control']['lifetime'] = '2h';
        $config->setConfig($this->config);
        $this->assertSame(7200, $config->getLifetime());

        $this->config['asset_manager']['cache_control']['lifetime'] = '3d';
        $config->setConfig($this->config);
        $this->assertSame(259200, $config->getLifetime());

        $this->config['asset_manager']['cache_control']['lifetime'] = '30';
        $config->setConfig($this->config);
        $this->assertEquals(30, $config->getLifetime());
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Valid formatters are d,h,m
     */
    public function testGetLifetimeWithInvalidFormatters()
    {
        $config = new Config();
        $this->config['asset_manager']['cache_control']['lifetime'] = '30p';
        $config->setConfig($this->config);

        $config->getLifetime();
    }
}
