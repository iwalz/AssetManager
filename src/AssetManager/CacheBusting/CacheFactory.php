<?php

namespace AssetManager\CacheBusting;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class for CacheBusting related Cache
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class CacheFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = new Config($serviceLocator->get('Config'));

        $cacheString = $config->getCache();
        $cacheClass = 'Assetic\Cache\\' . $cacheString . 'Cache';

        $cache = new $cacheClass;
        $cache->ttl = $config->getValidationLifetime();

        return $cache;
    }
}
