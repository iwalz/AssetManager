<?php

namespace AssetManagerTest;

use AssetManager\Helper\HeadScriptServiceFactory;
use PHPUnit_Framework_TestCase;
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
        $sm->setService('Config', array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enable' => true
                    )
                )
            )
        );
        $pm->setServiceLocator($sm);

        $headScript = $pm->get('headscript');
        $this->assertInstanceOf('AssetManager\Helper\HeadScript', $headScript);

    }
}
