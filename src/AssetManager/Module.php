<?php

namespace AssetManager;

use AssetManager\CacheBusting\Config;
use AssetManager\CacheBusting\RewriteContent;
use Zend\Http\Response;
use Zend\Loader\StandardAutoloader;
use Zend\Loader\AutoloaderFactory;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Module class
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    BootstrapListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Callback method to rewrite the img tags
     *
     * @param MvcEvent $event
     */
    public function onResponse(MvcEvent $event)
    {
        $response = $event->getResponse();
        $serviceManager     = $event->getApplication()->getServiceManager();

        $cacheBustingConfig = new Config($serviceManager->get('Config'));
        if ($cacheBustingConfig->isEnabled()) {
            $rewriteContent = new RewriteContent($response);
            $rewriteContent->setCache($serviceManager->get('AssetManager\CacheBusting\Cache'));
            $rewriteContent->addCacheBustingTag();
        }
    }

    /**
     * Callback method for dispatch and dispatch.error events.
     *
     * @param MvcEvent $event
     */
    public function onDispatch(MvcEvent $event)
    {
        $response = $event->getResponse();
        if (!method_exists($response, 'getStatusCode') || $response->getStatusCode() !== 404) {

            return;
        }

        $request            = $event->getRequest();
        $serviceManager     = $event->getApplication()->getServiceManager();
        $assetManager       = $serviceManager->get(__NAMESPACE__ . '\Service\AssetManager');
        $cacheController    = $assetManager->getCacheController();

        $cacheBusting       = $assetManager->getCacheBustingManager();

        if (!is_null($cacheBusting)) {
            $cacheBustingResponse = $cacheBusting->handleRequest();

            if ($cacheBustingResponse instanceof Response) {

                return $cacheBustingResponse;
            }
        }

        if (!$assetManager->resolvesToAsset($request)) {

            return;
        }

        if (!is_null($cacheController)) {
            $asset = $assetManager->resolve($request);
            $cachedResponse = $cacheController->handleRequest($asset);

            if (!is_null($cacheBusting)) {
                $cacheBusting->getConfig()->setAsset($asset);
            }

            if ($cachedResponse instanceof Response) {

                return $cachedResponse;
            }
        }

        $response->setStatusCode(200);

        return $assetManager->setAssetOnResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        // Attach for dispatch, and dispatch.error (with low priority to make sure statusCode gets set)
        $eventManager       = $event->getTarget()->getEventManager();
        $callback           = array($this, 'onDispatch');
        $rewriteResponse    = array($this, 'onResponse');
        $priority           = -9999999;
        $eventManager->attach(MvcEvent::EVENT_FINISH,         $rewriteResponse);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH,       $callback, $priority);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, $callback, $priority);
    }
}
