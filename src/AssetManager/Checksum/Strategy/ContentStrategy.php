<?php

namespace AssetManager\Checksum\Strategy;

/**
 * ContentStrategy returns an sha1 of the asset content
 * (recommended for assets using the less filter with variables e.g.)
 *
 * @package AssetManager\Checksum\Strategy
 */
class ContentStrategy extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    public function getChecksum()
    {
        return sha1($this->asset->dump());
    }
}
