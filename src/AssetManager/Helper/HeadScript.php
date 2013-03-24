<?php
namespace AssetManager\Helper;

use Zend\View\Helper\HeadScript as StandardHeadScript;
use Zend\ServiceManager\ServiceLocatorInterface;

class HeadScript extends StandardHeadScript
{
    /**
     * @var null|\Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sl = null;

    /**
     * {@inheritDoc}
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     */
    public function __construct(ServiceLocatorInterface $sl)
    {
        parent::__construct();
        $this->sl = $sl;
    }

    /**
     * {@inheritDoc}
     */
    public function toString($indent = null)
    {
        $value = parent::toString($indent);
        $container = $this->getContainer();

        /** @var $aggregateResolver \AssetManager\Resolver\AggregateResolver */
        $mainLocator = $this->sl->getServiceLocator();
        $aggregateResolver = $mainLocator->get('AssetManager\Service\AggregateResolver');
        $cacheController = $mainLocator->get('AssetManager\CacheControl\CacheController');

        foreach ($container as $element) {
            $source = $element->attributes["src"];
            $asset = $aggregateResolver->resolve($source);
            $value = str_replace($source, $source.';AM'.$cacheController->calculateEtag($asset), $value);
        }

        return $value;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->sl = $sl;
    }

    /**
     * Get the service locator
     *
     * @return null|\Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->sl;
    }
}
