<?php

namespace AssetManagerTest\Service;

use AssetManager\Service\AssetChecksumHandler;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetChecksumHandlerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testValidInstanceFromFactory()
    {
        $serviceManager = new ServiceManager();

        $factory = new \AssetManager\Service\AssetChecksumHandlerServiceFactory();
        $checksumHandler = $factory->createService($serviceManager);

        $this->assertTrue($checksumHandler instanceof AssetChecksumHandler);
    }
}
