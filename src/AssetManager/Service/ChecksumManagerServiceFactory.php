<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Exception;
use AssetManager\Service\ChecksumManager;

/**
 * Factory class for ChecksumManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class ChecksumManagerServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     *
     * @return ChecksumManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->get('Config');
        $config         = isset($config['asset_manager']) ? $config['asset_manager'] : array();

        $checksumManager = new ChecksumManager($config);

        return $checksumManager;
    }
}
