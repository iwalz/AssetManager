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
        $config         = $serviceLocator->get('Config');
        $config         = isset($config['asset_manager']) ? $config['asset_manager'] : array();

        $cacheController = new CacheController($config);
        $requestInspector = new RequestInspector();
        $responseModifier = new ResponseModifier();
        $cacheController->setRequestInspector($requestInspector);
        $cacheController->setResponseModifier($responseModifier);

        return $cacheController;
    }
}
