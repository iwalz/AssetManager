<?php

namespace AssetManagerTest;

use AssetManager\CacheBusting\CacheFactory;
use AssetManager\Helper\HeadScriptServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;
use Zend\View\Renderer\PhpRenderer as View;
use Zend\View\HelperPluginManager;
use Zend\ServiceManager\ServiceManager;

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
}
