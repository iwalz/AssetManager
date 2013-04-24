<?php

namespace AssetManager\CacheBusting;

use Assetic\Cache\CacheInterface;
use Zend\Stdlib\ResponseInterface;

class RewriteContent
{
    /**
     * @var ResponseInterface
     */
    protected $response = null;

    /**
     * @var CacheInterface
     */
    protected $cache = null;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return CacheInterface|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Rewrites the image tags inside the content
     */
    public function addCacheBustingTag()
    {
        $content = $this->response->getContent();
        $cache = $this->cache;
        $replace = function ($match) use ($cache) {
            $fileName = substr($match[1], strrpos($match[1], '/')+1);

            if ($cache->has($fileName . '_etag')) {

                return str_replace($fileName, $fileName . ";AM" . $cache->get($fileName . '_etag'), $match[0]);
            } else {

                return $match[0];
            }
        };
        $newContent = preg_replace_callback("/<img.*src=['\"]?(.*?)['\"\s>]/i", $replace, $content);
        $this->response->setContent($newContent);
    }
}
