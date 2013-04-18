<?php

namespace AssetManager\Checksum;

use AssetManager\CacheControl\Config;
use AssetManager\Checksum\Strategy\AbstractStrategyFactory;
use AssetManager\Checksum\Strategy\StaticStrategy;
use AssetManager\Checksum\Strategy\StrategyInterface;
use AssetManager\Exception\InvalidArgumentException;
use AssetManager\Service\AssetFilterManager;
use Zend\EventManager\Filter\FilterInterface;
use Assetic\Asset\AssetInterface;

/**
 * ChecksumHandler class
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class ChecksumHandler
{
    /**
     * @var array
     */
    protected $defaultStrategies = array();

    /**
     * Used strategy
     * @var int
     */
    protected $strategy         = null;

    /**
     * @var AssetInterface
     */
    protected $asset            = null;

    /**
     * @var AssetFilterManager
     */
    protected $assetFilterManager = null;

    /**
     * @var string
     */
    protected $path             = null;

    /**
     * @var \AssetManager\CacheControl\Config
     */
    protected $config           = null;

    /**
     * @param AssetFilterManager $assetFilterManager
     */
    public function setAssetFilterManager(AssetFilterManager $assetFilterManager)
    {
        $this->assetFilterManager = $assetFilterManager;
    }

    /**
     * @param string
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return AssetFilterManager|null
     */
    public function getAssetFilterManager()
    {
        return $this->assetFilterManager;
    }

    /**
     * Either
     *  - none
     *  - static
     *  - random
     *  - lastmodified
     *  - content
     *  - etag
     *  - or the classname of a class that implements AssetManager\Checksum\Strategy\StrategyInterface
     *
     * @param string|null|StrategyInterface The strategy that is used.
     */
    public function __construct($strategy = null)
    {
        if ($strategy !== null) {
            $this->setStrategy($strategy);
        }
    }

    /**
     * @param string|StrategyInterface $strategy
     */
    public function setStrategy($strategy)
    {
        if (!is_string($strategy) && !$strategy instanceof StrategyInterface) {
            throw new InvalidArgumentException('Only string or StrategyInterface implementation allowed');
        }

        $this->strategy = $strategy;
    }

    /**
     * @return string
     */
    public function getChecksum()
    {
        $strategy = AbstractStrategyFactory::factory($this->getStrategy());

        if (!is_null($this->assetFilterManager)) {
            $this->assetFilterManager->setFilters($this->path, $this->asset);
        }
        $strategy->setAsset($this->asset);

        if ( $strategy instanceof StaticStrategy && $this->config ) {
            $strategy->setStatic($this->config->getStatic());
        }

        return $strategy->getChecksum();
    }

    /**
     * @return int|null
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @param AssetInterface $asset
     */
    public function setAsset(AssetInterface $asset)
    {
        $this->asset = $asset;
    }

    /**
     * @return AssetInterface|null
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config|null
     */
    public function getConfig()
    {
        return $this->config;
    }
}
