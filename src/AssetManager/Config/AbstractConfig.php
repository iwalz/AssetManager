<?php

namespace AssetManager\Config;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Assetic\Asset\AssetInterface;

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
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->config               = $config;
        $this->allowGeneralConfig   = true;
        $this->allowAssetConfig     = true;
        $this->allowMimeConfig      = true;
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
        return $this->config['asset_manager'][static::getConfigKey()];
    }

    /**
     * @return ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->config);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->config);
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
     * The key below asset_manager
     *
     * @return string
     */
    public abstract function getConfigKey();
}
