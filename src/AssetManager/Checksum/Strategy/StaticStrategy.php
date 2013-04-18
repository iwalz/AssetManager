<?php

namespace AssetManager\Checksum\Strategy;

class StaticStrategy extends AbstractStrategy
{
    /**
     * @var string
     */
    protected $static = null;

    public function getChecksum()
    {
        return $this->static;
    }

    public function setStatic($static)
    {
        $this->static = $static;
    }
}
