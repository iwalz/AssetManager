<?php

namespace AssetManager\Checksum\Strategy;

class NoneStrategy extends AbstractStrategy
{
    public function getChecksum()
    {
        return "";
    }
}
