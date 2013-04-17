<?php

namespace AssetManagerTest;

use AssetManager\CacheBusting\CacheFactory;
use AssetManager\Helper\HeadLink;
use AssetManager\Helper\HeadLinkServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\Placeholder\Container;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer as View;
use Zend\View\Helper\HeadLink as StandardHeadLink;

class HeadLinkTest extends PHPUnit_Framework_TestCase
{
    public function testHeadLinkInSm()
    {
        $pm = new HelperPluginManager();
        $pm->setFactory('headlink', new HeadLinkServiceFactory());

        $sm = new ServiceManager();
        $sm->setService('Config', array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => true,
                        'cache' => 'Apc',
                        'lifetime' => 1000,
                        'validation_lifetime' => 30
                    )
                )
            )
        );
        $sm->setFactory('AssetManager\CacheBusting\Cache', new CacheFactory());
        $sm->setService('Request', new Request());
        $pm->setServiceLocator($sm);

        $headLink = $pm->get('headlink');
        $this->assertInstanceOf('AssetManager\Helper\HeadLink', $headLink);
    }

    public function testHeadLinkWithoutReplacement()
    {
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');
        $cache = $this->getMock('Assetic\Cache\ApcCache');

        $sm = new ServiceManager;

        $headLink = new HeadLink($sm, $request, $cache);

        $stdHeadLink = new StandardHeadLink();

        $this->assertEquals($stdHeadLink->prependStylesheet('/css/foo.css')->toString(), $headLink->prependStylesheet('/css/foo.css')->toString());
        $this->assertEquals($cache, $headLink->getCache());
        $this->assertEquals($sm, $headLink->getServiceLocator());
        $this->assertEquals($request, $headLink->getRequest());
    }

    public function testHeadLinkWithReplacement()
    {
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');
        $cache = $this->getMock('Assetic\Cache\ApcCache', array('get','has'));
        $cache->expects($this->any())->method('get')->will($this->returnValue('1234-test'));
        $cache->expects($this->any())->method('has')->with('foo.css_etag')->will($this->returnValue(true));

        $sm = new ServiceManager;
        $sm->setService('Request', new \Zend\Http\PhpEnvironment\Request());
        $sm->setService('AssetManager\CacheBusting\Cache', new CacheFactory());

        $headLink = new HeadLink($sm);
        $headLink->setRequest($request);
        $headLink->setCache($cache);
        $headLink->setServiceLocator($sm);

        $this->assertContains(';AM1234-test', $headLink()->prependStylesheet('/css/foo.css')->toString());
    }
}
