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
        $this->dispatch('/foo.js');
        $this->assertResponseStatusCode(200);
    }
}
