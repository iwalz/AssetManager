<?php

namespace AssetManager\Checksum;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChecksumHandlerServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $checksumHandler = new ChecksumHandler();

        return $checksumHandler;
    }

}
