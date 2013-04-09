<?php

namespace AssetManagerTest\CacheBusting;

use AssetManager\CacheBusting\Config;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetCacheBustingManagerTest extends PHPUnit_Framework_TestCase
{
    public function testCorrectConfigInterpretation()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => true
                    ),
                ),
            )
        );

        $tmp = new \AssetManager\CacheBusting\AssetCacheBustingManagerServiceFactory($serviceManager);
        $cacheBustingManager = $tmp->createService($serviceManager);

        $this->assertInstanceOf('AssetManager\Config\AbstractConfig', $cacheBustingManager->getConfig());

        $config = new Config(
            array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => false
                    ),
                ),
            )
        );
        $cacheBustingManager->setConfig($config);
        $this->assertFalse($cacheBustingManager->getConfig()->isEnabled());
    }

    public function testDefaultSettings()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => false
                    ),
                ),
            )
        );

        $tmp = new \AssetManager\CacheBusting\AssetCacheBustingManagerServiceFactory($serviceManager);
        $cacheBustingManager = $tmp->createService($serviceManager);

        $this->assertFalse($cacheBustingManager->getConfig()->isEnabled());
    }
}
