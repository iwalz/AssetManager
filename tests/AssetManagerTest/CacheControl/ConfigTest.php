<?php

namespace AssetManagerTest\CacheControl;

use AssetManager\CacheControl\Config;
use AssetManager\Service\MimeResolver;
use Assetic\Asset\FileAsset;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    protected $config = null;
    protected $cacheControlConfig = null;

    public function setUp()
    {
        $this->config = array(
            'asset_manager' => array(
                'cache_control' => array(
                    'lifetime' => '5m'
                )
            )
        );

        $asset = new FileAsset(__FILE__);
        $asset->mimetype = 'application/javascript';

        $this->cacheControlConfig = new Config();
        $this->cacheControlConfig->setAsset($asset);
        $this->cacheControlConfig->setMimeResolver(new MimeResolver());
        $this->cacheControlConfig->setPath('foo.jpg');
        $this->cacheControlConfig->setConfig($this->config);
    }

    public function testGetLifetimme()
    {
        $this->cacheControlConfig->setConfig($this->config);
        $this->assertSame(300, $this->cacheControlConfig->getLifetime());

        $this->config['asset_manager']['cache_control']['lifetime'] = '2h';
        $this->cacheControlConfig->setConfig($this->config);
        $this->assertSame(7200, $this->cacheControlConfig->getLifetime());

        $this->config['asset_manager']['cache_control']['lifetime'] = '3d';
        $this->cacheControlConfig->setConfig($this->config);
        $this->assertSame(259200, $this->cacheControlConfig->getLifetime());

        $this->config['asset_manager']['cache_control']['lifetime'] = '30';
        $this->cacheControlConfig->setConfig($this->config);
        $this->assertEquals(30, $this->cacheControlConfig->getLifetime());
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Valid formatters are d,h,m
     */
    public function testGetLifetimeWithInvalidFormatters()
    {
        $this->config['asset_manager']['cache_control']['lifetime'] = '30p';
        $this->cacheControlConfig->setConfig($this->config);

        $this->cacheControlConfig->getLifetime();
    }
}
