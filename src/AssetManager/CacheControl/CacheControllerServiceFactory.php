<?php

namespace AssetManager\CacheControl;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Exception;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class CacheControllerServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     *
     * @return CacheController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = new Config($serviceLocator->get('Config'));
        $config->setMimeResolver($serviceLocator->get('mime_resolver'));
        $cacheController = new CacheController($config);
        $requestInspector = new RequestInspector();
        $responseModifier = new ResponseModifier();
        $responseModifier->setConfig($config);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);

        return $cacheController;
    }
}
