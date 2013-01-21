<?php

namespace AssetManager\Service;

use AssetManager\Service\AssetChecksumHandler;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssetChecksumHandlerServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $assetChecksumHandler = new AssetChecksumHandler();

        return $assetChecksumHandler;
    }

}
