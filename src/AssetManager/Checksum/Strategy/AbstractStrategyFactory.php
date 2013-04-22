<?php

namespace AssetManager\Checksum\Strategy;

use AssetManager\Exception\InvalidArgumentException;

/**
 * A small abstract factory for fetching the strategies.
 * Performance related not done via ServiceManager
 *
 * @package AssetManager\Checksum\Strategy
 */
class AbstractStrategyFactory
{

    /**
     * @param string The name of the factory or a class name that inherits AbstractStrategy
     * @return \AssetManager\Checksum\Strategy\AbstractStrategy
     * @throws \AssetManager\Exception\InvalidArgumentException
     */
    public static function factory($name)
    {
        $defaultStrategies = array(
            'none'          => 'AssetManager\Checksum\Strategy\NoneStrategy',
            'static'        => 'AssetManager\Checksum\Strategy\StaticStrategy',
            'random'        => 'AssetManager\Checksum\Strategy\RandomStrategy',
            'lastmodified'  => 'AssetManager\Checksum\Strategy\LastModifiedStrategy',
            'content'       => 'AssetManager\Checksum\Strategy\ContentStrategy',
            'etag'          => 'AssetManager\Checksum\Strategy\EtagStrategy'
        );

        if (array_key_exists($name, $defaultStrategies)) {

            return new $defaultStrategies[$name];
        }

        if (!class_exists($name)) {

            throw new InvalidArgumentException('Class ' . $name . ' does not exist');
        }

        if (!in_array('AssetManager\Checksum\Strategy\StrategyInterface', class_implements($name))) {

            throw new InvalidArgumentException('Class ' . $name . ' must implement StrategyInterface');
        }

        return new $name;
    }
}
