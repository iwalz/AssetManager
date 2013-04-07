<?php

namespace AssetManager\Checksum\Strategy;

class EtagStrategy extends AbstractStrategy
{
    public function getChecksum()
    {
        $size = strlen($this->asset->dump());
        $mtime = base_convert(str_pad($this->asset->getLastModified(),16,"0"),10,16);

        return sprintf("%x-%s", $size, $mtime);
    }
}
