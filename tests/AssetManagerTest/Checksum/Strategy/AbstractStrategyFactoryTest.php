<?php

namespace AssetManagerTest\Checksum\Strategy;

use AssetManager\Checksum\Strategy\AbstractStrategyFactory;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;
use PHPUnit_Framework_TestCase;
use AssetManager\Checksum\Strategy\NoneStrategy;
use AssetManager\Checksum\Strategy\StaticStrategy;
use AssetManager\Checksum\Strategy\EtagStrategy;
use AssetManager\Checksum\Strategy\LastModifiedStrategy;
use AssetManager\Checksum\Strategy\ContentStrategy;
use AssetManager\Checksum\Strategy\RandomStrategy;

class AbstractStrategyFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testForCorrectInstances()
    {
        $this->assertInstanceOf('AssetManager\Checksum\Strategy\NoneStrategy', AbstractStrategyFactory::factory('none'));
        $this->assertInstanceOf('AssetManager\Checksum\Strategy\StaticStrategy', AbstractStrategyFactory::factory('static'));
        $this->assertInstanceOf('AssetManager\Checksum\Strategy\RandomStrategy', AbstractStrategyFactory::factory('random'));
        $this->assertInstanceOf('AssetManager\Checksum\Strategy\LastModifiedStrategy', AbstractStrategyFactory::factory('lastmodified'));
        $this->assertInstanceOf('AssetManager\Checksum\Strategy\ContentStrategy', AbstractStrategyFactory::factory('content'));
        $this->assertInstanceOf('AssetManager\Checksum\Strategy\EtagStrategy', AbstractStrategyFactory::factory('etag'));
    }

    public function testCustomStrategy()
    {
        require_once __DIR__ . '/../TestAsset/TestStrategy.php';
        $this->assertInstanceOf('AssetManagerTest\Checksum\TestAsset\TestStrategy', AbstractStrategyFactory::factory('AssetManagerTest\Checksum\TestAsset\TestStrategy'));
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Class AssetManagerTest\Checksum\TestAsset\FailStrategy must implement StrategyInterface
     */
    public function testCustomWrongInstanceStrategy()
    {
        require_once __DIR__ . '/../TestAsset/FailStrategy.php';
        AbstractStrategyFactory::factory('AssetManagerTest\Checksum\TestAsset\FailStrategy');
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Class Abcde does not exist
     */
    public function testCustomNonExistentInstanceStrategy()
    {
        AbstractStrategyFactory::factory('Abcde');
    }

    public function testEtagStrategy()
    {
        $asset = $this->getMock('Assetic\Asset\FileAsset', array('getLastModified', 'dump'), array(__FILE__));
        $asset->expects($this->once())->method('dump')->will($this->returnValue('foo'));
        $asset->expects($this->once())->method('getLastModified')->will($this->returnValue(1365344767));

        $strategy = new EtagStrategy();
        $strategy->setAsset($asset);
        $this->assertEquals('3-4d9c619d53dc0', $strategy->getChecksum());
    }
}
