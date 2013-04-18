<?php

namespace AssetManager\Checksum\Strategy;

class LastModifiedStrategy extends AbstractStrategy
{
    public function getChecksum()
    {
        return $this->asset->getLastModified();
    }
}
