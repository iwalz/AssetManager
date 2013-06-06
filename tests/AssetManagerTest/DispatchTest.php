<?php

namespace AssetManagerTest;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;

class DispatchTest extends AbstractControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../_files/application.config.php'
        );
        parent::setUp();
    }

    public function testStatusCode304OnIfModifiedSinceRequest()
    {
        $config = $this->getApplicationServiceLocator()->get('Config');
        $config['asset_manager']['cache_control']['enabled'] = true;
        $this->getApplicationServiceLocator()->setAllowOverride(true);
        $this->getApplicationServiceLocator()->setService('Config', $config);

        $request = $this->getApplication()->getRequest();
        $request->getHeaders()->addHeaderLine('If-Modified-Since', date("D,d M Y H:i:s T", time()));
        $this->dispatch('/foo.js');
        $this->assertResponseStatusCode(304);
        $content = $this->getApplication()->getResponse()->getContent();
        $this->assertEquals('', $content);
    }

    public function testStatusCode200OnIfModifiedSinceRequest()
    {
        $config = $this->getApplicationServiceLocator()->get('Config');
        $config['asset_manager']['cache_control']['enabled'] = true;
        $config['asset_manager']['cache_busting']['enabled'] = false;
        $config['asset_manager']['cache_busting']['validation_lifetime'] = 60;
        $this->getApplicationServiceLocator()->setAllowOverride(true);
        $this->getApplicationServiceLocator()->setService('Config', $config);
        $request = $this->getApplication()->getRequest();
        $request->getHeaders()->addHeaderLine('If-Modified-Since', 'Sat, 19 Dec 2000 16:25:03 GMT');
        $this->dispatch('/foo.js');
        $this->assertResponseStatusCode(200);
        $content = $this->getApplication()->getResponse()->getContent();
        $this->assertEquals("alert('JS File');" . PHP_EOL, $content);
    }

    public function testStatusCode304OnCacheBustingRequest()
    {
        $config = $this->getApplicationServiceLocator()->get('Config');
        $this->assertFalse($config['asset_manager']['cache_control']['enabled']);

        $config['asset_manager']['cache_busting']['enabled'] = true;
        $config['asset_manager']['cache_busting']['validation_lifetime'] = 60;
        $this->getApplicationServiceLocator()->setAllowOverride(true);
        $this->getApplicationServiceLocator()->setService('Config', $config);

        $request = $this->getApplication()->getRequest();
        $request->getHeaders()->addHeaderLine('If-Modified-Since', date("D,d M Y H:i:s T", time()));
        $this->dispatch('/foo.css;AM1-2-3-4');
        $this->assertResponseStatusCode(304);
        $content = $this->getApplication()->getResponse()->getContent();
        $this->assertEquals('', $content);
    }

    public function testStatusCode200OnCacheBustingRequest()
    {
        $this->getApplicationServiceLocator()->setAllowOverride(true);
        $config = $this->getApplicationServiceLocator()->get('Config');
        $this->assertFalse($config['asset_manager']['cache_control']['enabled']);
        $resolver = $this->getMock('AssetManager\Service\AggregateResolver', array('resolve'));
        $resolver->expects($this->never())->method('resolve');
        $this->getApplicationServiceLocator()->setService('AssetManager\Resolver    \AggregateResolver', $resolver);

        $config['asset_manager']['cache_busting']['enabled'] = true;
        $config['asset_manager']['cache_busting']['validation_lifetime'] = 60;

        $this->getApplicationServiceLocator()->setService('Config', $config);
        $this->dispatch('/foo.css');
        $this->assertResponseStatusCode(200);
        $content = $this->getApplication()->getResponse()->getContent();
        $this->assertEquals('.foo { }' . PHP_EOL, $content);
    }
}
