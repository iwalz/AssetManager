<?php

namespace AssetManager\Checksum\Strategy;

/**
 * LastModifiedStrategy gives you the last modification timestamp of an asset
 *
 * @package AssetManager\Checksum\Strategy
 */
class LastModifiedStrategy extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    public function getChecksum()
    {
        return $this->asset->getLastModified();
    }
}
