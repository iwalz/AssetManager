<?php

namespace AssetManagerTest\CacheBusting;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetCacheBustingManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
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

        $this->assertTrue($cacheBustingManager instanceof \AssetManager\CacheBusting\AssetCacheBustingManager);
    }
}
