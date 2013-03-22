<?php

namespace AssetManager\Service;

/**
 * ChecksumManager class
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class ChecksumManager
{
    /**
     * No hash
     */
    const STRATEGY_NONE         = 0;
    /**
     * Hash based on a static value (e.g. a version number)
     */
    const STRATEGY_STATIC       = 1;
    /**
     * Random hash (for cache-versioning)
     */
    const STRATEGY_RANDOM       = 2;
    /**
     * Hash based on last modified timestamp
     */
    const STRATEGY_LASTMODIFIED = 3;
    /**
     * Hash based on the content checksum
     */
    const STRATEGY_CONTENT      = 4;
    /**
     * ETag checksum
     */
    const STRATEGY_ETAG         = 5;
    /**
     * Used strategy
     * @var int
     */
    protected $strategy         = null;

    /**
     * @param int $strategy
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @return int|null
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}
