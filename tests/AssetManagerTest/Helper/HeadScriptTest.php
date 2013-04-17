<?php

namespace AssetManagerTest;

use AssetManager\CacheBusting\CacheFactory;
use AssetManager\Helper\HeadScriptServiceFactory;
use AssetManager\Helper\HeadScript;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;
use Zend\View\Renderer\PhpRenderer as View;
use Zend\View\HelperPluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\HeadScript as StandardHeadScript;

class HeadScriptTest extends PHPUnit_Framework_TestCase
{
    public function testRewriteHeadScriptContentWithCacheBusting()
    {
        $pm = new HelperPluginManager();
        $pm->setFactory('headscript', new HeadScriptServiceFactory());

        $sm = new ServiceManager();
        $sm->setFactory('AssetManager\CacheBusting\Cache', new CacheFactory());
        $sm->setService('Config', array(
            'asset_manager' => array(
                'cache_busting' => array(
                    'enabled' => true,
                    'cache' => 'Apc',
                    'validation_lifetime' => 60
                )
            )
        )
        );
        $sm->setService('Request', new Request());
        $pm->setServiceLocator($sm);

        $headScript = $pm->get('headscript');
        $this->assertInstanceOf('AssetManager\Helper\HeadScript', $headScript);
    }

    public function testHeadScriptWithoutReplacement()
    {
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');
        $cache = $this->getMock('Assetic\Cache\ApcCache');

        $sm = new ServiceManager;

        $headScript = new HeadScript($sm, $request, $cache);
        $stdHeadScript = new StandardHeadScript();

        $this->assertEquals($stdHeadScript->prependFile('/js/foo.js')->toString(), $headScript->prependFile('/js/foo.js')->toString());
        $this->assertEquals($cache, $headScript->getCache());
        $this->assertEquals($sm, $headScript->getServiceLocator());
        $this->assertEquals($request, $headScript->getRequest());
    }

    public function testHeadScriptWithReplacement()
    {
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');
        $cache = $this->getMock('Assetic\Cache\ApcCache', array('get','has'));
        $cache->expects($this->any())->method('get')->will($this->returnValue('1234-test'));
        $cache->expects($this->any())->method('has')->with('foo.js_etag')->will($this->returnValue(true));

        $sm = new ServiceManager;
        $sm->setService('Request', new \Zend\Http\PhpEnvironment\Request());
        $sm->setService('AssetManager\CacheBusting\Cache', new CacheFactory());

        $headScript = new HeadScript($sm);
        $headScript->setRequest($request);
        $headScript->setCache($cache);
        $headScript->setServiceLocator($sm);

        $this->assertContains(';AM1234-test', $headScript()->prependFile('/js/foo.js')->toString());
    }
}
