<?php
namespace AssetManager\Helper;

use Assetic\Cache\CacheInterface;
use Zend\View\Helper\HeadLink as StandardHeadLink;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Placeholder\Container;
use Zend\Http\PhpEnvironment\Request;

class HeadLink extends StandardHeadLink
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
        $this->request = !is_null($request) ? $request : $this->sl->get('Request');
        $this->cache = !is_null($cache) ? $cache : $this->sl->get('AssetManager\CacheBusting\Cache');
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
            $href = $include->href;
            $fileName = substr($href, strrpos($href, '/')+1);

            if ($this->cache->has($fileName . '_etag')) {
                $include->href = $href . ';AM' . $this->cache->get($fileName . '_etag');
                $newContainer->append($include);
                continue;
            }

            $newContainer->append($include);
        }

        parent::setContainer($newContainer);

        return parent::toString($indent);
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
