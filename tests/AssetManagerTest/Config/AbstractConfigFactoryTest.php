<?php

namespace AssetManagerTest\Config;

use AssetManager\Config\AbstractConfigFactory;
use AssetManager\Service\MimeResolver;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\Config as ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

class AbstractConfigFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $serviceManager = null;

    public function setUp()
    {
        $cfg = include __DIR__.'/../../../config/module.config.php';
        $cfg = array_merge($cfg, array(
                'asset_manager' => array(
                    'cache_control' => array(
                        'lifetime' => '5m'
                    )
                )
            )
        );

        $serviceManagerConfig = new ServiceManagerConfig($cfg['service_manager']);
        $this->serviceManager = new ServiceManager($serviceManagerConfig);
        $this->serviceManager->setService('Config', $cfg);
    }

    public function testCorrectInstanceFromFactory()
    {
        $testConfig = $this->serviceManager->get('AssetManager\CacheControl\Config');
        $this->assertInstanceOf('AssetManager\CacheControl\Config', $testConfig);
        $this->assertInstanceOf('AssetManager\Config\AbstractConfig', $testConfig);
    }

    /**
     * @expectedException Zend\ServiceManager\Exception\ServiceNotFoundException
     * @expectedExceptionMessage Zend\ServiceManager\ServiceManager::get was unable to fetch or create an instance for Foo
     */
    public function testInvalidInstanceFromFactory()
    {
        $this->serviceManager->get('Foo');
    }

    /**
     * @expectedException Zend\ServiceManager\Exception\ServiceNotFoundException
     * @expectedExceptionMessage Zend\ServiceManager\ServiceManager::get was unable to fetch or create an instance for stdClass
     */
    public function testInvalidParentInstanceFromFactory()
    {
        $this->serviceManager->get('stdClass');
    }

    public function testDependenciesInObjectFromFactory()
    {
        $testConfig = $this->serviceManager->get('AssetManager\CacheControl\Config');
        $testConfig->enableAssetConfig(false);
        $testConfig->enableMimeConfig(false);
        $testConfig->enableExtensionConfig(false);

        $this->assertSame(300, $testConfig->getLifetime());

        $this->assertEquals(new MimeResolver(), $testConfig->getMimeResolver());
    }
}
