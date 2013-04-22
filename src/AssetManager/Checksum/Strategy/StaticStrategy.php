<?php

namespace AssetManager\Checksum\Strategy;

/**
 * StaticStrategy handles the static hashes.
 * For example a logo of a website - you can cache it forever, but link it
 * to the current version of your website. In the next release you may change it ...
 *
 * @package AssetManager\Checksum\Strategy
 */
class StaticStrategy extends AbstractStrategy
{
    /**
     * @var string
     */
    protected $static = null;

    /**
     * {@inheritDoc}
     */
    public function getChecksum()
    {
        return $this->static;
    }

    /**
     * @param string the static string (version e.g.)
     */
    public function setStatic($static)
    {
        $this->static = $static;
    }
}
