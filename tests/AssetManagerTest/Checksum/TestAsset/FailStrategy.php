<?php

namespace AssetManagerTest\Checksum\TestAsset;

class FailStrategy
{
    public function getChecksum()
    {
        return "fail";
    }
}
