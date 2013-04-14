<?php

namespace AssetManagerTest\CacheBusting;

use AssetManager\CacheBusting\Config;
use AssetManager\CacheControl\CacheControllerServiceFactory;
use AssetManager\Service\MimeResolver;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\ServiceManager;

class AssetCacheBustingManagerTest extends PHPUnit_Framework_TestCase
{
    public function testCorrectConfigInterpretation()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setFactory('AssetManager\CacheControl\CacheController', new CacheControllerServiceFactory());
        $serviceManager->setService('mime_resolver', new MimeResolver());
        $serviceManager->setService('Request', new Request());
        $serviceManager->setService('Response', new Response());
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => true,
                        'cache' => 'Apc'
                    ),
                ),
            )
        );
        $serviceManager->setService('AssetManager\CacheBusting\Cache', $this->getMock('Assetic\Cache\ApcCache'));

        $tmp = new \AssetManager\CacheBusting\AssetCacheBustingManagerServiceFactory($serviceManager);
        $cacheBustingManager = $tmp->createService($serviceManager);

        $this->assertInstanceOf('AssetManager\Config\AbstractConfig', $cacheBustingManager->getConfig());
        $this->assertInstanceOf('Assetic\Cache\ApcCache', $cacheBustingManager->getCacheController()->getResponseModifier()->getCache());

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
        $serviceManager->setFactory('AssetManager\CacheControl\CacheController', new CacheControllerServiceFactory());
        $serviceManager->setService('AssetManager\CacheBusting\Cache', $this->getMock('Assetic\Cache\ApcCache'));
        $serviceManager->setService('mime_resolver', new MimeResolver());
        $serviceManager->setService('Request', new Request());
        $serviceManager->setService('Response', new Response());
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => false
                    ),
                    'cache_control' => array(
                        'enabled' => false
                    )
                ),
            )
        );

        $tmp = new \AssetManager\CacheBusting\AssetCacheBustingManagerServiceFactory($serviceManager);
        $cacheBustingManager = $tmp->createService($serviceManager);

        $this->assertFalse($cacheBustingManager->getConfig()->isEnabled());
    }
}
