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
        $globalConfig   = $serviceLocator->get('Config');
        $config         = new Config($globalConfig);

        $assetCacheBustingManager = new AssetCacheBustingManager($config);

        return $assetCacheBustingManager;
    }
}
