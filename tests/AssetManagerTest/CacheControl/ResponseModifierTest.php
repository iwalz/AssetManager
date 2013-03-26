<?php

namespace AssetManagerTest\CacheControl;

use AssetManager\CacheControl\Config;
use AssetManager\CacheControl\ResponseModifier;
use PHPUnit_Framework_TestCase;
use Zend\Http\Headers;
use Zend\Http\Response;

class ResponseModifierTest extends PHPUnit_Framework_TestCase
{
    public function testGettersAndSetters()
    {
        $responseModifier = new ResponseModifier();
        $config = new Config();
        $response = new Response();

        $responseModifier->setResponse($response);
        $responseModifier->setConfig($config);

        $this->assertSame($config, $responseModifier->getConfig());
        $this->assertSame($response, $responseModifier->getResponse());
    }

    public function testEnableNotModified()
    {
        $responseModifier = new ResponseModifier();
        $headers = new Headers();

        $response = $this->getMock('Zend\Http\Response', array('getHeaders', 'setStatusCode'));
        $response->expects($this->once())->method('getHeaders')->will($this->returnValue($headers));
        $response->expects($this->once())->method('setStatusCode')->with(304);

        $responseModifier->setResponse($response);
        $responseModifier->enableNotModified();
    }

    public function testRemoveCacheControlOnNotModifiedResponse()
    {
        $responseModifier = new ResponseModifier();
        $headers = new Headers();
        $headers->addHeaderLine('Cache-Control', 'public');

        $response = $this->getMock('Zend\Http\Response', array('getHeaders'));
        $response->expects($this->once())->method('getHeaders')->will($this->returnValue($headers));

        $responseModifier->setResponse($response);
        $this->assertTrue($headers->has('Cache-Control'));
        $responseModifier->enableNotModified();
        $this->assertFalse($headers->has('Cache-Control'));
    }
}
