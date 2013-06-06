<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\CacheControl\Config as CacheControlConfig;
use AssetManager\CacheBusting\Config as CacheBustingConfig;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetManagerServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     *
     * @return AssetManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config             = $serviceLocator->get('Config');
        $assetManagerConfig = array();

        if (!empty($config['asset_manager'])) {
            $assetManagerConfig = $config['asset_manager'];
        }

        $assetManager = new AssetManager(
            $serviceLocator->get('AssetManager\Service\AggregateResolver'),
            $assetManagerConfig
        );

        $assetManager->setAssetFilterManager($serviceLocator->get('AssetManager\Service\AssetFilterManager'));

        $assetManager->setAssetCacheManager($serviceLocator->get('AssetManager\Service\AssetCacheManager'));

        $cacheControlConfig = new CacheControlConfig($config);
        $cacheBustingConfig = new CacheBustingConfig($config);
        var_dump($cacheBustingConfig);

        if ($cacheControlConfig->isEnabled()) {
            $assetManager->setCacheController($serviceLocator->get('AssetManager\CacheControl\CacheController'));
        }

        if ($cacheBustingConfig->isEnabled()) {
            $assetManager->setCacheBustingManager($serviceLocator->get('AssetManager\CacheBusting\AssetCacheBustingManager'));
        }

        return $assetManager;
    }

}
