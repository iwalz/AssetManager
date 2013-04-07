<?php

namespace AssetManager\CacheControl;

use AssetManager\Checksum\ChecksumHandler;
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

        if (!$config->isEnabled()) {

            return false;
        }

        $config->setMimeResolver($serviceLocator->get('mime_resolver'));
        $cacheController = new CacheController($config);
        $requestInspector = new RequestInspector();
        $responseModifier = new ResponseModifier();
        $checksumHandler = new ChecksumHandler();
        $responseModifier->setChecksumHandler($checksumHandler);
        $requestInspector->setChecksumHandler($checksumHandler);
        $responseModifier->setConfig($config);
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);

        return $cacheController;
    }
}
