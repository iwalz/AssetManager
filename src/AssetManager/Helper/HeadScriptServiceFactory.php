<?php

namespace AssetManager\Helper;

use AssetManager\Helper\HeadScript;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\HeadScript as StandardHeadScript;

/**
 * Factory class for HeadScript
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class HeadScriptServiceFactory implements FactoryInterface
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

        if ( isset($config['cache_busting']['enabled']) ) {

            return new HeadScript($serviceLocator->getServiceLocator());
        }

        return new StandardHeadScript();
    }
}
