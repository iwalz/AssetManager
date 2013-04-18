<?php

namespace AssetManager\Checksum\Strategy;

class ContentStrategy extends AbstractStrategy
{
    public function getChecksum()
    {
        return sha1($this->asset->dump());
    }
}
