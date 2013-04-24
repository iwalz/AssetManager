<?php

namespace AssetManagerTest\CacheBusting;

use AssetManager\CacheBusting\RewriteContent;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Response;

class RewriteContentTest extends PHPUnit_Framework_TestCase
{
    public function testGettersAndSetters()
    {
        $response = new Response();
        $cache = $this->getMock('Assetic\Cache\CacheInterface');
        $rewriteContent = new RewriteContent($response);
        $rewriteContent->setCache($cache);

        $this->assertSame($cache, $rewriteContent->getCache());
    }

    public function testContentRewriteWithStrangeSyntax()
    {
        $content = "adasgrsad<IMG sRc=foo.png alt='foo'/>GDASADSxknmx";
        $response = new Response();
        $cache = $this->getMock('Assetic\Cache\ApcCache', array('has', 'get'));
        $cache->expects($this->once())->method('has')->will($this->returnValue(true));
        $cache->expects($this->once())->method('get')->will($this->returnValue('123-45678'));
        $response->setContent($content);

        $rewriteContent = new RewriteContent($response);
        $rewriteContent->setCache($cache);
        $rewriteContent->addCacheBustingTag();

        $this->assertEquals("adasgrsad<IMG sRc=foo.png;AM123-45678 alt='foo'/>GDASADSxknmx", $response->getContent());
    }

    public function testContentRewrite()
    {
        $content = "<img src='test.png'/>";
        $response = new Response();
        $cache = $this->getMock('Assetic\Cache\ApcCache', array('has', 'get'));
        $cache->expects($this->once())->method('has')->will($this->returnValue(true));
        $cache->expects($this->once())->method('get')->will($this->returnValue('123-45678'));
        $response->setContent($content);

        $rewriteContent = new RewriteContent($response);
        $rewriteContent->setCache($cache);
        $rewriteContent->addCacheBustingTag();

        $this->assertEquals("<img src='test.png;AM123-45678'/>", $response->getContent());
    }
}
