<?php

namespace AssetManager\Config;

use AssetManager\Exception\InvalidArgumentException;
use AssetManager\Service\MimeResolver;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Assetic\Asset\AssetInterface;
use Zend\Http\Request;

abstract class AbstractConfig implements Countable, IteratorAggregate
{
    /**
     * @var array
     */
    protected $config = array();
    /**
     * @var AssetInterface
     */
    protected $asset = null;
    /**
     * @var MimeResolver
     */
    protected $mimeResolver = null;
    /**
     * @var bool
     */
    protected $allowGeneralConfig = null;
    /**
     * @var bool
     */
    protected $allowAssetConfig = null;
    /**
     * @var bool
     */
    protected $allowMimeConfig = null;
    /**
     * @var null
     */
    protected $allowExtensionConfig = null;
    /**
     * @var string
     */
    protected $path = null;

    /**
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->config               = $config;
        $this->allowGeneralConfig   = true;
        $this->allowAssetConfig     = true;
        $this->allowMimeConfig      = true;
        $this->allowExtensionConfig = true;
    }

    /**
     * @param $bool
     */
    public function enableGeneralConfig($bool)
    {
        $this->allowGeneralConfig = $bool;
    }

    /**
     * @param $bool
     */
    public function enableAssetConfig($bool)
    {
        $this->allowAssetConfig = $bool;
    }

    /**
     * @param $bool
     */
    public function enableMimeConfig($bool)
    {
        $this->allowMimeConfig = $bool;
    }

    /**
     * @param $bool
     */
    public function enableExtensionConfig($bool)
    {
        $this->allowExtensionConfig = $bool;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $globalConfig = $this->config['asset_manager'];
        $config = array();

        if (
            $this->allowGeneralConfig
            && !empty($globalConfig[static::getConfigKey()])
        ) {
            $config = array_merge($config, $globalConfig[static::getConfigKey()]);
        }

        if ( $this->allowExtensionConfig ) {
            if (
                $this->getMimeResolver() === null
                || $this->getAsset() === null
            ) {
                throw new InvalidArgumentException('Asset and MimeResolver need to be set for the ExtensionConfig');
            }

            $extension = $this->getMimeResolver()->getExtension($this->asset->mimetype);
            if (!empty($globalConfig[$extension][static::getConfigKey()])) {
                $config = array_merge($config, $globalConfig[$extension][static::getConfigKey()]);
            }
        }

        if ( $this->allowMimeConfig ) {
            if (
                $this->getMimeResolver() === null
                || $this->getAsset() === null
            ) {
                throw new InvalidArgumentException('Asset and MimeResolver need to be set for the MimeConfig');
            }

            if (!empty($globalConfig[$this->asset->mimetype][static::getConfigKey()])) {
                $config = array_merge($config, $globalConfig[$this->asset->mimetype][static::getConfigKey()]);
            }
        }

        if ( $this->allowAssetConfig ) {
            if (
                $this->getPath() === null || $this->getPath() == ''
            ) {
                throw new InvalidArgumentException('Path/Request need to be set for the AssetConfig');
            }

            if (!empty($globalConfig[$this->getPath()][static::getConfigKey()])) {
                $config = array_merge($config, $globalConfig[$this->getPath()][static::getConfigKey()]);
            }
        }

        return $config;
    }

    /**
     * @return ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getConfig());
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->getConfig());
    }

    /**
     * @param AssetInterface $asset
     */
    public function setAsset(AssetInterface $asset)
    {
        $this->asset = $asset;
    }

    /**
     * @return AssetInterface|null
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * @param MimeResolver $mimeResolver
     */
    public function setMimeResolver( MimeResolver $mimeResolver)
    {
        $this->mimeResolver = $mimeResolver;
    }

    /**
     * @return MimeResolver|null
     */
    public function getMimeResolver()
    {
        return $this->mimeResolver;
    }

    /**
     * @param string|Request $path
     * @throws \AssetManager\Exception\InvalidArgumentException
     */
    public function setPath($path)
    {
        if (is_string($path)) {
            $this->path = $path;

            return;
        }

        if ($path instanceof Request) {
            $uri            = $path->getUri();
            $fullPath       = $uri->getPath();
            $basePathLen    = strlen($path->getBasePath()) + 1;
            $this->path     = ltrim(substr($fullPath, $basePathLen), '/');

            return;
        }

        throw new InvalidArgumentException('Only strings or Zend\Http\Request is allowed');
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * The key below asset_manager
     *
     * @return string
     */
    public abstract function getConfigKey();
}
