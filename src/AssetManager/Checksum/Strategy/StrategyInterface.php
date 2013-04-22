<?php

namespace AssetManager\Checksum\Strategy;

/**
 * StrategyInterface
 * @package AssetManager\Checksum\Strategy
 */
interface StrategyInterface
{
    /**
     * Get the checksum
     *
     * @return string
     */
    public function getChecksum();
}
