<?php
namespace AssetManager\Helper;

use Zend\View\Helper\HeadLink as StandardHeadLink;
use Zend\ServiceManager\ServiceLocatorInterface;

class HeadLink extends StandardHeadLink
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
        /** @var $aggregateResolver \AssetManager\Resolver\AggregateResolver */
        $mainLocator = $this->sl->getServiceLocator();
        $aggregateResolver = $mainLocator->get('AssetManager\Service\AggregateResolver');
        $cacheController = $mainLocator->get('AssetManager\Service\CacheController');

        $assetManager = $mainLocator->get('AssetManager\Service\AssetManager');
        $filterManager = $mainLocator->get('AssetManager\Service\AssetFilterManager');
        if (!$cacheController->hasMagicEtag()) {
            return $value;
        }

        $container = $this->getContainer();
        foreach ($container as $element) {
            $asset = $aggregateResolver->resolve($element->href);
            $factory = new \Assetic\Factory\AssetFactory($asset->getSourceRoot());
            $checksum = $factory->generateAssetName($asset, $filterManager->getFilters($asset->getSourceRoot(), $asset));
            $value = str_replace($element->href, $element->href.';ETag'.$checksum, $value);
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
