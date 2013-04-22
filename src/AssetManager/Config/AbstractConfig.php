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
     * The whole module config, used as a base
     *
     * @var array
     */
    protected $config = array();

    /**
     * The parsed config
     *
     * @var array
     */
    protected $internalConfig = null;

    /**
     * The asset, needed for asset configuration
     *
     * @var AssetInterface
     */
    protected $asset = null;

    /**
     * The MimeResolver for mime specific configuration
     *
     * @var MimeResolver
     */
    protected $mimeResolver = null;

    /**
     * Enable/Disable configuration on the root level
     *
     * @var bool
     */
    protected $allowGeneralConfig = null;

    /**
     * Enable/Disable asset configuration
     *
     * @var bool
     */
    protected $allowAssetConfig = null;

    /**
     * Enable/Disable mime configuration
     *
     * @var bool
     */
    protected $allowMimeConfig = null;

    /**
     * Enable/Disable the extension configuration
     *
     * @var null
     */
    protected $allowExtensionConfig = null;

    /**
     * The path, relevant to know which config section to match
     *
     * @var string
     */
    protected $path = null;

    /**
     * @var boolean
     */
    protected $enableGlobalConfig = null;

    /**
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->setConfig( $config );
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
        if ($bool !== $this->allowGeneralConfig) {
            $this->allowGeneralConfig = $bool;
            $this->reset();
        }
    }

    /**
     * @param $bool
     */
    public function enableAssetConfig($bool)
    {
        if ($bool !== $this->allowAssetConfig) {
            $this->allowAssetConfig = $bool;
            $this->reset();
        }
    }

    /**
     * @param $bool
     */
    public function enableMimeConfig($bool)
    {
        if ($bool !== $this->allowMimeConfig) {
            $this->allowMimeConfig = $bool;
            $this->reset();
        }
    }

    /**
     * @param $bool
     */
    public function enableExtensionConfig($bool)
    {
        if ($bool !== $this->allowExtensionConfig) {
            $this->allowExtensionConfig = $bool;
            $this->reset();
        }
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        if ($config !== $this->config) {
            $this->config = $config;
            $this->reset();
        }
    }

    /**
     * Get the config based on the configuration and
     * behaviour settings
     *
     * @return array
     */
    public function getConfig($validation = true)
    {
        if ($this->internalConfig !== null) {
            return $this->internalConfig;
        }

        $globalConfig = $this->config['asset_manager'];
        $config = array();

        if (
            $this->allowGeneralConfig
            && !empty($globalConfig[static::getConfigKey()])
        ) {
            $config = array_merge($config, $globalConfig[static::getConfigKey()]);
        }

        if ( $this->allowExtensionConfig && !$this->enableGlobalConfig) {
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

        if ( $this->allowMimeConfig && !$this->enableGlobalConfig ) {
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

        if ( $this->allowAssetConfig && !$this->enableGlobalConfig ) {
            if (
                $this->getPath() === null || $this->getPath() == ''
            ) {
                throw new InvalidArgumentException('Path/Request need to be set for the AssetConfig');
            }

            if (!empty($globalConfig[$this->getPath()][static::getConfigKey()])) {
                $config = array_merge($config, $globalConfig[$this->getPath()][static::getConfigKey()]);
            }
        }

        if ($validation) {
            $this->checkRequiredKeys($config);
        }
        $this->internalConfig = $config;

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
     * Reset the config merge
     */
    public function reset()
    {
        $this->internalConfig = null;
    }

    /**
     * @param AssetInterface $asset
     */
    public function setAsset(AssetInterface $asset)
    {
        if ($asset !== $this->asset) {
            $this->asset = $asset;
            $this->reset();
        }
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
        $this->reset();
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
     * Check if all required keys are present
     */
    private function checkRequiredKeys(array $config)
    {
        foreach(static::getRequiredKeys() as $requiredKey) {
            if (!array_key_exists($requiredKey, $config)) {
                throw new InvalidArgumentException("Config key '$requiredKey' is missing in the config");
            }
        }
    }

    /**
     * If true, the config only get the default setting - but don't need
     * any further dependencies
     */
    protected function enableGlobalConfig($bool = true)
    {
        $this->enableGlobalConfig = $bool;
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
    abstract public function getConfigKey();

    /**
     * The required keys
     *
     * @return string
     */
    abstract public function getRequiredKeys();
}
