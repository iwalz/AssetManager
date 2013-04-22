<?php

namespace AssetManager\Checksum\Strategy;

/**
 * EtagStrategy simulates the apache etag, but does not contain the inode information
 *
 * @package AssetManager\Checksum\Strategy
 */
class EtagStrategy extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    public function getChecksum()
    {
        $size = strlen($this->asset->dump());
        $mtime = base_convert(str_pad($this->asset->getLastModified(),16,"0"),10,16);

        return sprintf("%x-%s", $size, $mtime);
    }
}
