<?php

namespace AssetManagerTest;

use AssetManager\CacheControl\Config;
use AssetManager\CacheControl\RequestInspector;
use AssetManager\CacheControl\ResponseModifier;
use Assetic\Asset\StringAsset;
use PHPUnit_Framework_TestCase;
use AssetManager\Module;
use Zend\Http\Response;
use Zend\Http\PhpEnvironment\Request;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;

/**
* @covers AssetManager\Module
*/
class ModuleTest extends PHPUnit_Framework_TestCase
{
    public function testGetAutoloaderConfig()
    {
        $module = new Module();
        // just testing ZF specification requirements
        $this->assertInternalType('array', $module->getAutoloaderConfig());
    }

    public function testGetConfig()
    {
        $module = new Module();
        // just testing ZF specification requirements
        $this->assertInternalType('array', $module->getConfig());
    }

    /**
     * Verifies that dispatch listener does nothing on other repsponse codes
     */
    public function testDispatchListenerIgnoresOtherResponseCodes()
    {
        $event      = new MvcEvent();
        $response   = new Response();
        $module     = new Module();

        $response->setStatusCode(500);
        $event->setResponse($response);

        $response = $module->onDispatch($event);

        $this->assertNull($response);
    }

    public function testOnDispatchDoesntResolveToAsset()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'getCacheController'), array($resolver));
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(false));

        $cacheController = $this->getMock('AssetManager\CacheControl\CacheController');
        $assetManager
            ->expects($this->once())
            ->method('getCacheController')
            ->will($this->returnValue($cacheController));


        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event      = new MvcEvent();
        $response   = new Response();
        $request    = new Request();
        $module     = new Module();

        $event->setApplication($application);
        $response->setStatusCode(404);
        $event->setResponse($response);
        $event->setRequest($request);

        $return = $module->onDispatch($event);

        $this->assertNull($return);
    }

    public function testOnDispatchStatus200()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'setAssetOnResponse', 'getCacheController', 'resolve'), array($resolver));
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(true));

        $cacheController = $this->getMock('AssetManager\CacheControl\CacheController');
        $assetManager
            ->expects($this->once())
            ->method('getCacheController')
            ->will($this->returnValue($cacheController));

        $assetManager
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue(new StringAsset('foo')));

        $amResponse = new Response();
        $amResponse->setContent('bacon');

        $assetManager
            ->expects($this->once())
            ->method('setAssetOnResponse')
            ->will($this->returnValue($amResponse));

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event      = new MvcEvent();
        $response   = new Response();
        $request    = new Request();
        $module     = new Module();

        $event->setApplication($application);
        $response->setStatusCode(404);
        $event->setResponse($response);
        $event->setRequest($request);

        $return = $module->onDispatch($event);

        $this->assertEquals(200, $return->getStatusCode());
    }

    protected function getCacheController()
    {
        $cacheController = $this->getMock('AssetManager\CacheControl\CacheController', array('handleRequest'));
        $responseModifier = new ResponseModifier();
        $requestInspector = new RequestInspector();
        $config = new Config(
            array(
                'asset_manager' => array(
                    'cache_control' => array(
                        'lifetime' => '5m'
                    )
                )
            )
        );
        $cacheController->setResponseModifier($responseModifier);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setConfig($config);
        $response = new Response();
        $response->setStatusCode(304);
        $cacheController->expects($this->any())->method('handleRequest')->will($this->returnValue($response));

        return $cacheController;
    }

    public function testOnDispatchModifiedSinceRequestWith304()
    {
        $event      = new MvcEvent();
        $request    = new Request();
        $module     = new Module();
        $response   = new Response();
        $response->setStatusCode(404);
        $time = 'Sat, 19 Jan 2013 16:25:03 GMT';
        $request->getHeaders()->addHeaderLine('If-Modified-Since', $time);

        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'setAssetOnResponse', 'resolve', 'getCacheController'), array($resolver));
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(true));

        $cacheController = $this->getCacheController();
        $cacheController->setRequest($request);
        $cacheController->setResponse($response);
        $assetManager
            ->expects($this->once())
            ->method('getCacheController')
            ->will($this->returnValue($cacheController));

        $asset = new \Assetic\Asset\StringAsset("foo");
        $asset->setLastModified(strtotime($time));
        $assetManager
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($asset));

        $amResponse = new Response();
        $amResponse->setContent('bacon');

        $assetManager
            ->expects($this->exactly(0))
            ->method('setAssetOnResponse');


        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event->setApplication($application);
        $event->setRequest($request);
        $event->setResponse($response);

        $return = $module->onDispatch($event);

        $this->assertSame(304, $return->getStatusCode());
    }

    /**
     * @covers \AssetManager\Module::onDispatch
     */
    public function testWillIgnoreInvalidResponseType()
    {
        $cliResponse = $this->getMock('Zend\Console\Response', array(), array(), '', false);
        $mvcEvent   = $this->getMock('Zend\Mvc\MvcEvent');
        $module     = new Module();

        $cliResponse->expects($this->never())->method('getStatusCode');
        $mvcEvent->expects($this->once())->method('getResponse')->will($this->returnValue($cliResponse));

        $this->assertNull($module->onDispatch($mvcEvent));
    }

    public function testOnBootstrap()
    {
        $applicationEventManager = new EventManager();

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($applicationEventManager));

        $event = new Event();
        $event->setTarget($application);

        $module = new Module();
        $module->onBootstrap($event);

        $dispatchListeners = $applicationEventManager->getListeners(MvcEvent::EVENT_DISPATCH);

        foreach ($dispatchListeners as $listener) {
            $metaData = $listener->getMetadata();
            $callback = $listener->getCallback();

            $this->assertEquals('onDispatch', $callback[1]);
            $this->assertEquals(-9999999, $metaData['priority']);
            $this->assertTrue($callback[0] instanceof Module);

        }

        $dispatchListeners = $applicationEventManager->getListeners(MvcEvent::EVENT_DISPATCH_ERROR);

        foreach ($dispatchListeners as $listener) {
            $metaData = $listener->getMetadata();
            $callback = $listener->getCallback();

            $this->assertEquals('onDispatch', $callback[1]);
            $this->assertEquals(-9999999, $metaData['priority']);
            $this->assertTrue($callback[0] instanceof Module);

        }
    }
}
