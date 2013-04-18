<?php

namespace AssetManager\CacheBusting;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class for AssetCacheBustingManager
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetCacheBustingManagerServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AssetCacheBustingManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $globalConfig   = $serviceLocator->get('Config');
        $config         = new Config($globalConfig);
        $cacheControllerConfig = new CacheControllerConfig($globalConfig);
        $cacheControllerConfig->setMimeResolver($serviceLocator->get('mime_resolver'));
        $cacheControllerConfig->setPath($serviceLocator->get('Request'));

        $assetCacheBustingManager = new AssetCacheBustingManager($config);
        $cacheController = $serviceLocator->get('AssetManager\CacheControl\CacheController');
        $cacheController->setConfig($cacheControllerConfig);
        $cache = $serviceLocator->get('AssetManager\CacheBusting\Cache');

        $cacheController->getResponseModifier()->setCache($cache);
        $assetCacheBustingManager->setCacheController($cacheController);

        return $assetCacheBustingManager;
    }
}
