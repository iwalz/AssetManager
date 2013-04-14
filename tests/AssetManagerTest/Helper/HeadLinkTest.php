<?php

namespace AssetManagerTest;

use AssetManager\CacheBusting\CacheFactory;
use AssetManager\Helper\HeadLinkServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer as View;

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
}
