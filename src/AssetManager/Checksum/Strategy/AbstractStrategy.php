<?php

namespace AssetManager\Checksum\Strategy;

use Assetic\Asset\AssetInterface;

abstract class AbstractStrategy implements StrategyInterface
{
    /**
     * @var AssetInterface
     */
    protected $asset = null;

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
