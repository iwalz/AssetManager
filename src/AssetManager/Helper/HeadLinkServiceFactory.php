<?php

namespace AssetManager\Helper;

use AssetManager\Helper\HeadLink;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\HeadLink as StandardHeadLink;

/**
 * Factory class for HeadLink
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class HeadLinkServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     *
     * @return \Zend\View\Helper\HeadLink
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->getServiceLocator()->get('Config');
        $config         = isset($config['asset_manager']) ? $config['asset_manager'] : array();

        if (
            isset($config['cache_busting']['enabled'])
            && $config['cache_busting']['enabled']
        ) {

            return new HeadLink($serviceLocator->getServiceLocator());
        }

        return new StandardHeadLink();
    }
}
