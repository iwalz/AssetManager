<?php

namespace AssetManagerTest\Service;

use AssetManager\Service\AssetCacheManager;
use AssetManager\Service\AssetCacheManagerServiceFactory;
use Assetic\Asset\StringAsset;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetCacheManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage You need to set the path and the asset to detect the cache settings
     */
    public function testGetCacheInstanceWithMissingParameter()
    {
        $assetCacheManager = new AssetCacheManager(
            array(
                'asset_manager' => array(
                    'caching' => array(
                        'default' => array(
                            'cache' => 'Apc',
                        ),
                    ),
                ),
            )
        );
        $assetCacheManager->getCacheInstance();
    }

    public function testSameInstanceOnGetCacheInstance()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'caching' => array(
                        'default' => array(
                            'cache' => 'Apc',
                        ),
                    ),
                ),
            )
        );

        $t = new AssetCacheManagerServiceFactory($serviceManager);

        $assetCacheManager = $t->createService($serviceManager);

        $asset = new StringAsset('foo');
        $firstInstance = $assetCacheManager->getCacheInstance('/foo', $asset);
        $secondAsset = $assetCacheManager->getCacheInstance();

        $this->assertSame($firstInstance, $secondAsset);
    }
}
