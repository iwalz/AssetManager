<?php

namespace AssetManagerTest\CacheControl;

use AssetManager\CacheControl\CacheController;
use AssetManager\CacheControl\ResponseModifier;
use AssetManager\Checksum\ChecksumHandler;
use Assetic\Asset\FileAsset;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\Response;

class CacheControllerTest extends PHPUnit_Framework_TestCase
{
    public function testGettersAndSetters()
    {
        $cacheController = new CacheController();
        $responseModifier = $this->getMock('AssetManager\CacheControl\ResponseModifier');
        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector');
        $config = $this->getMock('AssetManager\CacheControl\Config');

        $request = new Request();
        $response = new Response();

        $responseModifier->expects($this->once())->method('setResponse');
        $responseModifier->expects($this->once())->method('setConfig');
        $config->expects($this->once())->method('setPath');

        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);
        $cacheController->setConfig($config);

        $cacheController->setResponse($response);
        $cacheController->setRequest($request);

        $this->assertSame($requestInspector, $cacheController->getRequestInspector());
        $this->assertSame($responseModifier, $cacheController->getResponseModifier());
        $this->assertSame($config, $cacheController->getConfig());
    }

    public function testHandleRequestWithModifiedSinceValidation()
    {
        $cacheController = new CacheController();
        $responseModifier = new ResponseModifier();
        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isIfModifiedSinceRequest', 'getModifiedSince'));
        $config = $this->getMock('AssetManager\CacheControl\Config');

        $request = new Request();
        $response = new Response();

        $responseModifier->setResponse($response);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);
        $cacheController->setConfig($config);
        $cacheController->setRequest($request);
        $cacheController->setResponse($response);

        $asset = $this->getMock('Assetic\Asset\FileAsset', array('getLastModified'), array(__FILE__));
        $asset->expects($this->once())->method('getLastModified')->will($this->returnValue(1));

        $requestInspector->expects($this->any())->method('isIfModifiedSinceRequest')->will($this->returnValue(true));
        $requestInspector->expects($this->any())->method('getModifiedSince')->will($this->returnValue(3));

        $cachedResponse = $cacheController->handleRequest($asset);

        $this->assertSame(304, $cachedResponse->getStatusCode());
    }

    public function testHandleRequestWithModifiedSince()
    {
        $cacheController = new CacheController();
        $responseModifier = new ResponseModifier();
        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isIfModifiedSinceRequest', 'getModifiedSince'));
        $config = $this->getMock('AssetManager\CacheControl\Config');

        $request = new Request();
        $response = new Response();

        $responseModifier->setResponse($response);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);
        $cacheController->setConfig($config);
        $cacheController->setRequest($request);
        $cacheController->setResponse($response);

        $asset = $this->getMock('Assetic\Asset\FileAsset', array('getLastModified'), array(__FILE__));
        $asset->expects($this->once())->method('getLastModified')->will($this->returnValue(3));

        $requestInspector->expects($this->any())->method('isIfModifiedSinceRequest')->will($this->returnValue(true));
        $requestInspector->expects($this->any())->method('getModifiedSince')->will($this->returnValue(1));

        $cachedResponse = $cacheController->handleRequest($asset);

        $this->assertNull($cachedResponse);
    }

    public function testHandleRequestWithCacheBustingAndIfModifiedSince()
    {
        $cacheController = new CacheController();
        $responseModifier = new ResponseModifier();
        $checksumHandler = new ChecksumHandler();
        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isIfNoneMatchRequest', 'isCacheBustingRequest'));
        $config = $this->getMock('AssetManager\CacheControl\Config');

        $request = new Request();
        $request->getHeaders()->addHeaderLine('If-None-Match', '1234-test');
        $request->getHeaders()->addHeaderLine('If-Modified-Since', 'Tue, 15 Jan 2013 17:58:53 GMT');
        $response = new Response();

        $responseModifier->setResponse($response);
        $responseModifier->setChecksumHandler($checksumHandler);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);
        $cacheController->setConfig($config);
        $cacheController->setRequest($request);
        $cacheController->setResponse($response);

        $asset = $this->getMock('Assetic\Asset\FileAsset', array('getLastModified'), array(__FILE__));

        $requestInspector->expects($this->any())->method('isIfNoneMatchRequest')->will($this->returnValue(true));
        $requestInspector->expects($this->any())->method('isCacheBustingRequest')->will($this->returnValue(true));

        $cachedResponse = $cacheController->handleRequest($asset);

        $this->assertSame(304, $cachedResponse->getStatusCode());
    }

    public function testHandleRequestWithCacheBustingAndIfModifiedSinceWithCache()
    {
        $cacheController = new CacheController();
        $responseModifier = new ResponseModifier();
        $cache = $this->getMock('Assetic\Cache\ApcCache');
        $responseModifier->setCache($cache);
        $checksumHandler = $this->getMock('AssetManager\Checksum\ChecksumHandler', array('getChecksum'));
        $checksumHandler->expects($this->once())->method('getChecksum')->will($this->returnValue('1234-test'));
        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isIfNoneMatchRequest', 'isCacheBustingRequest'));
        $config = $this->getMock('AssetManager\CacheControl\Config');

        $request = new Request();
        $request->getHeaders()->addHeaderLine('If-None-Match', '1234-tes');
        $request->getHeaders()->addHeaderLine('If-Modified-Since', 'Tue, 15 Jan 2013 17:58:53 GMT');
        $response = new Response();

        $responseModifier->setResponse($response);
        $responseModifier->setChecksumHandler($checksumHandler);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);
        $cacheController->setConfig($config);
        $cacheController->setRequest($request);
        $cacheController->setResponse($response);

        $asset = $this->getMock('Assetic\Asset\FileAsset', array('getLastModified'), array(__FILE__));
        $asset->expects($this->exactly(2))->method('getLastModified')->will($this->returnValue(12345));

        $requestInspector->expects($this->any())->method('isIfNoneMatchRequest')->will($this->returnValue(false));
        $requestInspector->expects($this->any())->method('isCacheBustingRequest')->will($this->returnValue(true));

        $cachedResponse = $cacheController->handleRequest($asset);

        $this->assertSame(304, $cachedResponse->getStatusCode());
    }

    public function testHandleRequestWithCacheBustingIfNoneMatch()
    {
        $cacheController = new CacheController();
        $responseModifier = new ResponseModifier();
        $checksumHandler = $this->getMock('AssetManager\Checksum\ChecksumHandler', array('getChecksum'));
        $checksumHandler->setStrategy('etag');
        $checksumHandler->expects($this->once())->method('getChecksum')->will($this->returnValue('1234-test'));

        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isIfNoneMatchRequest', 'isCacheBustingRequest'));
        $config = $this->getMock('AssetManager\CacheControl\Config');

        $request = new Request();
        $request->getHeaders()->addHeaderLine('If-None-Match', '1234-test');
        $response = new Response();

        $responseModifier->setResponse($response);
        $responseModifier->setChecksumHandler($checksumHandler);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);
        $cacheController->setConfig($config);
        $cacheController->setRequest($request);
        $cacheController->setResponse($response);

        $asset = $this->getMock('Assetic\Asset\FileAsset', array('getLastModified'), array(__FILE__));

        $requestInspector->expects($this->any())->method('isIfNoneMatchRequest')->will($this->returnValue(true));
        $requestInspector->expects($this->any())->method('isCacheBustingRequest')->will($this->returnValue(true));

        $cachedResponse = $cacheController->handleRequest($asset);
        $this->assertSame(304, $cachedResponse->getStatusCode());
    }

    public function testHandleRequestWithCacheBustingIfNoneMatchAndCache()
    {
        $cacheController = new CacheController();
        $responseModifier = new ResponseModifier();
        $cache = $this->getMock('Assetic\Cache\ApcCache');
        $responseModifier->setCache($cache);
        $checksumHandler = $this->getMock('AssetManager\Checksum\ChecksumHandler', array('getChecksum'));
        $checksumHandler->setStrategy('etag');
        $checksumHandler->expects($this->exactly(2))->method('getChecksum')->will($this->returnValue('1234-test'));

        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isIfNoneMatchRequest', 'isCacheBustingRequest'));
        $config = $this->getMock('AssetManager\CacheControl\Config');

        $request = new Request();
        $request->getHeaders()->addHeaderLine('If-None-Match', '1234-test');
        $response = new Response();

        $responseModifier->setResponse($response);
        $responseModifier->setChecksumHandler($checksumHandler);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);
        $cacheController->setConfig($config);
        $cacheController->setRequest($request);
        $cacheController->setResponse($response);

        $asset = $this->getMock('Assetic\Asset\FileAsset', array('getLastModified'), array(__FILE__));

        $requestInspector->expects($this->any())->method('isIfNoneMatchRequest')->will($this->returnValue(true));
        $requestInspector->expects($this->any())->method('isCacheBustingRequest')->will($this->returnValue(true));

        $cachedResponse = $cacheController->handleRequest($asset);
        $this->assertSame(304, $cachedResponse->getStatusCode());
    }

    public function testHandleRequestWithCacheBustingWithoutIfNoneMatch()
    {
        $cacheController = new CacheController();
        $responseModifier = new ResponseModifier();
        $requestInspector = $this->getMock('AssetManager\CacheControl\RequestInspector', array('isCacheBustingRequest', 'stripCacheBustingTag'));
        $config = $this->getMock('AssetManager\CacheControl\Config');

        $request = new Request();
        $response = new Response();

        $responseModifier->setResponse($response);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);
        $cacheController->setConfig($config);
        $cacheController->setRequest($request);
        $cacheController->setResponse($response);

        $asset = $this->getMock('Assetic\Asset\FileAsset', array('getLastModified'), array(__FILE__));

        $requestInspector->expects($this->any())->method('isCacheBustingRequest')->will($this->returnValue(true));

        $cacheController->handleRequest($asset);
    }
}
