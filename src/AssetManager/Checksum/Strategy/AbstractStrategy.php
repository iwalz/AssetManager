<?php

namespace AssetManager\Checksum\Strategy;

use Assetic\Asset\AssetInterface;

/**
 * Abstract strategy implementation. Use this to write custom
 * checksum strategies
 *
 * @package AssetManager\Checksum\Strategy
 */
abstract class AbstractStrategy implements StrategyInterface
{
    /**
     * @var AssetInterface
     */
    protected $asset = null;

    /**
     * Get the checksum for the injected asset
     *
     * @return string
     */
    abstract public function getChecksum();

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
}
