<?php

namespace AssetManager\CacheBusting;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssetCacheBustingManagerServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AssetCacheBustingManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cacheBusting = array();
        $config  = $serviceLocator->get('Config');

        if (!empty($config['asset_manager']['cache_busting'])) {
            $cacheBusting = $config['asset_manager']['cache_busting'];
        }

        $assetCacheBustingManager = new AssetCacheBustingManager($cacheBusting);

        return $assetCacheBustingManager;
    }
}
