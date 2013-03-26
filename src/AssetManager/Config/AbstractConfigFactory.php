<?php

namespace AssetManager\Config;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\AbstractFactoryInterface;

class AbstractConfigFactory implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(
        ServiceLocatorInterface $serviceLocator,
        $name,
        $requestedName)
    {
        if( !class_exists($requestedName)) {

            return false;
        }

        $parents = class_parents($requestedName);

        if ($parents) {

            return in_array('AssetManager\Config\AbstractConfig', $parents);
        }

        return false;
    }

    public function createServiceWithName (
        ServiceLocatorInterface $serviceLocator,
        $name,
        $requestedName)
    {
        $reflect = new \ReflectionClass($requestedName);
        $config = $reflect->newInstanceArgs(array($serviceLocator->get('Config')));
        $config->setMimeResolver($serviceLocator->get('mime_resolver'));

        return $config;
    }
}
