<?php

namespace AssetManagerTest\CacheControl;

use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use AssetManager\Service\MimeResolver;
use PHPUnit_Framework_TestCase;
use AssetManager\CacheControl\CacheControllerServiceFactory;
use AssetManager\CacheControl\CacheController;
use Assetic\Asset\StringAsset;
use Zend\ServiceManager\ServiceManager;
use AssetManager\CacheControl\Config as CacheControllerConfig;

class CacheControllerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $config = array();

    public function setUp()
    {
        $this->config = array(
            'asset_manager' => array(
                'cache_control' => array(
                    'lifetime' => '5m',
                    'etag' => true,
                    'enabled' => true
                ),
                'cache_busting' => array(
                    'enabled' => false
                )
            )
        );
    }

    public function testCorrectConfigInstanceInService()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $this->config);
        $serviceManager->setService('mime_resolver', new MimeResolver());
        $serviceManager->setService('Request', new Request());
        $serviceManager->setService('Response', new Response());


        $factory = new CacheControllerServiceFactory();
        $cacheController = $factory->createService($serviceManager);
        $this->assertTrue($cacheController instanceof CacheController);
        $config = $cacheController->getConfig();

        $this->assertTrue($config instanceof CacheControllerConfig);
    }
}
