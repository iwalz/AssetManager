<?php

namespace AssetManagerTest\Checksum\TestAsset;

use AssetManager\Checksum\Strategy\AbstractStrategy;

class TestStrategy extends AbstractStrategy
{
    public function getChecksum()
    {
        return "test";
    }
}
