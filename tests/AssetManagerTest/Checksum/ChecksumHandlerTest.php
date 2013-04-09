<?php

namespace AssetManagerTest\Checksum;

use AssetManager\Checksum\ChecksumHandler;
use AssetManager\Checksum\ChecksumHandlerServiceFactory;
use Assetic\Asset\FileAsset;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class ChecksumHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testValidInstanceFromFactory()
    {
        $checksumHandler = new ChecksumHandler();
        $checksumHandler->setStrategy('etag');

        $asset = $this->getMock('Assetic\Asset\FileAsset', array('getLastModified', 'dump'), array(__FILE__));
        $asset->expects($this->once())->method('dump')->will($this->returnValue('foo'));
        $asset->expects($this->once())->method('getLastModified')->will($this->returnValue(1365344767));

        $checksumHandler->setAsset($asset);

        $this->assertEquals('3-4d9c619d53dc0', $checksumHandler->getChecksum());
    }

    public function testFilterExecution()
    {
        $checksumHandler = new ChecksumHandler('etag');
        $filterManager = $this->getMock('AssetManager\Service\AssetFilterManager', array('setFilters'));
        $asset = $this->getMock('Assetic\Asset\StringAsset', array(), array('foo'));

        $filterManager->expects($this->once())->method('setFilters');

        $checksumHandler->setAsset($asset);
        $checksumHandler->setPath('/foo.css');
        $checksumHandler->setAssetFilterManager($filterManager);

        $checksumHandler->getChecksum();

        $this->assertEquals('/foo.css', $checksumHandler->getPath());
        $this->assertEquals($asset, $checksumHandler->getAsset());
        $this->assertEquals($filterManager, $checksumHandler->getAssetFilterManager());
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Only string or StrategyInterface implementation allowed
     */
    public function testWrongStrategy()
    {
        $checksumHandler = new ChecksumHandler();
        $checksumHandler->setStrategy(new \stdClass());
    }
}
