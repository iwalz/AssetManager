<?php

namespace AssetManager\CacheControl;

interface CacheControllerAwareInterface
{
    public function setCacheController(CacheController $cacheController);
}
