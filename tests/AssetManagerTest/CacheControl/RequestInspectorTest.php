<?php

namespace AssetManagerTest\CacheControl;

use AssetManager\CacheControl\RequestInspector;
use PHPUnit_Framework_TestCase;
use Zend\Http\Header\IfNoneMatch;
use Zend\Http\Headers;
use Zend\Uri\Uri;

class RequestInspectorTest extends PHPUnit_Framework_TestCase
{
    public function testIsCacheBustingRequest()
    {
        $request = $this->getMock('Zend\Http\Request', array('getUri'));
        $uri = new Uri();
        $uri->setPath('http://www.foo.com/bar.jpg;AMusdmfsowu2323kF');
        $request->expects($this->any())->method('getUri')->will($this->returnValue($uri));
        $requestInspector = new RequestInspector($request);

        $this->assertTrue($requestInspector->isCacheBustingRequest());

        $uri->setPath('http://www.foo.com/bar.jpg');
        $this->assertFalse($requestInspector->isCacheBustingRequest());
        $this->assertSame($request, $requestInspector->getRequest());
    }

    public function testIsIfNoneMatchRequest()
    {
        $request = $this->getMock('Zend\Http\Request', array('getHeaders'));
        $headers = new Headers();
        $headers->addHeaderLine('If-None-Match', 'abcde');
        $request->expects($this->any())->method('getHeaders')->will($this->returnValue($headers));
        $requestInspector = new RequestInspector($request);

        $this->assertTrue($requestInspector->isIfNoneMatchRequest());

        $headers->clearHeaders();
        $this->assertFalse($requestInspector->isIfNoneMatchRequest());
    }

    public function testIsIfModifiedSinceRequest()
    {
        $request = $this->getMock('Zend\Http\Request', array('getHeaders'));
        $headers = new Headers();
        $headers->addHeaderLine('If-Modified-Since', 'abcde');
        $request->expects($this->any())->method('getHeaders')->will($this->returnValue($headers));
        $requestInspector = new RequestInspector($request);

        $this->assertTrue($requestInspector->isIfModifiedSinceRequest());

        $headers->clearHeaders();
        $this->assertFalse($requestInspector->isIfModifiedSinceRequest());
    }

    public function testGetModifiedSince()
    {
        $request = $this->getMock('Zend\Http\Request', array('getHeaders'));
        $headers = new Headers();
        $headers->addHeaderLine('If-Modified-Since', 'Tue, 15 Jan 2013 17:58:53 GMT');
        $request->expects($this->any())->method('getHeaders')->will($this->returnValue($headers));
        $requestInspector = new RequestInspector($request);

        $this->assertSame(1358272733, $requestInspector->getModifiedSince());
    }

    public function testMoreComplexRequestSetupWithMixedRequests()
    {
        $request = $this->getMock('Zend\Http\Request', array('getHeaders', 'getUri'));

        $headers = new Headers();
        $headers->addHeaderLine('If-Modified-Since', 'test');
        $request->expects($this->any())->method('getHeaders')->will($this->returnValue($headers));

        $uri = new Uri();
        $uri->setPath('http://www.foo.com/bar.jpg;AMusdmfsowu2323kF');
        $request->expects($this->any())->method('getUri')->will($this->returnValue($uri));

        $requestInspector = new RequestInspector($request);

        $this->assertTrue($requestInspector->isIfModifiedSinceRequest());
        $this->assertTrue($requestInspector->isCacheBustingRequest());

        $headers->clearHeaders();
        $this->assertFalse($requestInspector->isIfModifiedSinceRequest());
        $this->assertTrue($requestInspector->isCacheBustingRequest());

        $uri->setPath('http://www.foo.com/bar.jpg');
        $this->assertFalse($requestInspector->isCacheBustingRequest());
    }

    public function testStripCacheBustingTag()
    {
        $request = $this->getMock('Zend\Http\Request', array('getUri'));
        $uri = new Uri();
        $uri->setPath('http://example.com/foo.jpg;AMsafsadgter');
        $request->expects($this->any())->method('getUri')->will($this->returnValue($uri));
        $requestInspector = new RequestInspector($request);

        $this->assertTrue($requestInspector->isCacheBustingRequest());
        $requestInspector->stripCacheBustingTag();

        $this->assertEquals('http://example.com/foo.jpg', $uri->getPath());
    }
}
