<?php
namespace AssetManager\Helper;

use Zend\View\Helper\HeadScript as StandardHeadScript;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Placeholder\Container;

class HeadScript extends StandardHeadScript
{
    /**
     * @var null|\Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sl = null;

    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @var CacheInterface
     */
    protected $cache = null;

    /**
     * {@inheritDoc}
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     */
    public function __construct(ServiceLocatorInterface $sl, Request $request = null, CacheInterface $cache = null)
    {
        parent::__construct();
        $this->sl = $sl;
        $this->request = !is_null($request) ?: $this->sl->get('Request');
        $this->cache = !is_null($cache) ?: $this->sl->get('AssetManager\CacheBusting\Cache');
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return array|null|object
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return array|null|object
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * {@inheritDoc}
     */
    public function toString($indent = null)
    {
        $newContainer = new Container();

        foreach(parent::getContainer() as $include) {
            $src = $include->attributes["src"];
            $fileName = substr($src, strrpos($src, '/')+1);

            if ($this->cache->has($fileName . '_etag')) {
                $include->attributes["src"] = $src . ';AM' . $this->cache->get($fileName . '_etag');
                $newContainer->append($include);
                continue;
            }

            $newContainer->append($include);
        }

        parent::setContainer($newContainer);

        return parent::toString();
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
