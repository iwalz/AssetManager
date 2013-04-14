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
        $this->getApplicationServiceLocator()->setAllowOverride(true);
        $this->getApplicationServiceLocator()->setService('Config', $config);
        $request = $this->getApplication()->getRequest();
        $request->getHeaders()->addHeaderLine('If-Modified-Since', 'Sat, 19 Dec 2000 16:25:03 GMT');
        $this->dispatch('/foo.js');
        $this->assertResponseStatusCode(200);
        $content = $this->getApplication()->getResponse()->getContent();
        $this->assertEquals("alert('JS File');" . PHP_EOL, $content);
    }
}
