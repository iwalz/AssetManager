<?php

namespace AssetManagerTest\CacheBusting;

use AssetManager\CacheControl\CacheControllerServiceFactory;
use AssetManager\Service\MimeResolver;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\ServiceManager;

class AssetCacheBustingManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setFactory('AssetManager\CacheControl\CacheController', new CacheControllerServiceFactory());
        $serviceManager->setService('mime_resolver', new MimeResolver());
        $serviceManager->setService('AssetManager\CacheBusting\Cache', $this->getMock('Assetic\Cache\ApcCache'));
        $serviceManager->setService('Request', new Request());
        $serviceManager->setService('Response', new Response());

        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => true
                    ),
                    'cache_control' => array(
                        'enabled' => false
                    )
                ),
            )
        );

        $tmp = new \AssetManager\CacheBusting\AssetCacheBustingManagerServiceFactory($serviceManager);
        $cacheBustingManager = $tmp->createService($serviceManager);

        $this->assertTrue($cacheBustingManager instanceof \AssetManager\CacheBusting\AssetCacheBustingManager);
    }
}
