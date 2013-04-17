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

    public function testHandleNotCacheBustingRequest()
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

        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isCacheBustingRequest'));
        $requestInspector->expects($this->once())->method('isCacheBustingRequest')->will($this->returnValue(false));
        $cacheBustingManager->getCacheController()->setRequestInspector($requestInspector);

        $this->assertNull($cacheBustingManager->handleRequest());
    }

    public function testHandleCacheBustingRequestNotModified()
    {
        $request = new Request();
        $request->setUri('css/bootstrap.min.css;AM1234-test');
        $request->getHeaders()->addHeaderLine('If-None-Match', '1234-test');

        $serviceManager = new ServiceManager();
        $serviceManager->setFactory('AssetManager\CacheControl\CacheController', new CacheControllerServiceFactory());
        $serviceManager->setService('AssetManager\CacheBusting\Cache', $this->getMock('Assetic\Cache\ApcCache'));
        $serviceManager->setService('mime_resolver', new MimeResolver());
        $serviceManager->setService('Request', $request);
        $serviceManager->setService('Response', new Response());
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => true,
                        'cache' => 'Apc',
                        'validation_lifetime' => 50
                    )
                ),
            )
        );

        $tmp = new \AssetManager\CacheBusting\AssetCacheBustingManagerServiceFactory($serviceManager);
        $cacheBustingManager = $tmp->createService($serviceManager);

        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isCacheBustingRequest'));
        $requestInspector->expects($this->once())->method('isCacheBustingRequest')->will($this->returnValue(true));
        $requestInspector->setRequest($serviceManager->get('Request'));
        $cacheBustingManager->getCacheController()->setRequestInspector($requestInspector);

        $response = $cacheBustingManager->handleRequest();
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $response);
        $this->assertSame(304, $response->getStatusCode());
    }

    public function testHandleCacheBustingRequest()
    {
        $request = new Request();
        $request->setUri('css/bootstrap.min.css;AM1234-test');

        $serviceManager = new ServiceManager();
        $serviceManager->setFactory('AssetManager\CacheControl\CacheController', new CacheControllerServiceFactory());
        $serviceManager->setService('AssetManager\CacheBusting\Cache', $this->getMock('Assetic\Cache\ApcCache'));
        $serviceManager->setService('mime_resolver', new MimeResolver());
        $serviceManager->setService('Request', $request);
        $serviceManager->setService('Response', new Response());
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => true,
                        'cache' => 'Apc',
                        'validation_lifetime' => 50
                    )
                ),
            )
        );

        $tmp = new \AssetManager\CacheBusting\AssetCacheBustingManagerServiceFactory($serviceManager);
        $cacheBustingManager = $tmp->createService($serviceManager);

        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isCacheBustingRequest'));
        $requestInspector->expects($this->once())->method('isCacheBustingRequest')->will($this->returnValue(true));
        $requestInspector->setRequest($serviceManager->get('Request'));
        $cacheBustingManager->getCacheController()->setRequestInspector($requestInspector);

        $this->assertEquals('css/bootstrap.min.css;AM1234-test', $request->getUri()->getPath());
        $response = $cacheBustingManager->handleRequest();
        $this->assertNull($response);
        $this->assertEquals('css/bootstrap.min.css', $request->getUri()->getPath());
    }
}
