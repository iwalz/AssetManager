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
    const STRATEGY_NONE         = 0;
    const STRATEGY_STATIC       = 1;
    const STRATEGY_RANDOM       = 2;
    const STRATEGY_LASTMODIFIED = 4;
    const STRATEGY_CONTENT      = 8;
    const STRATEGY_ETAG         = 16;
}
