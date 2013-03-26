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
