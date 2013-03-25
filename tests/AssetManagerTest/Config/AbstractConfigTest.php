<?php

namespace AssetManagerTest\Config;

use AssetManager\Service\MimeResolver;
use Assetic\Asset\FileAsset;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;
use Zend\Uri\Uri;

class AbstractConfigTest extends PHPUnit_Framework_TestCase
{
    protected $mockConfig = null;
    protected $dummyConfig = array();

    public function setUp()
    {
        $this->mockConfig = $this->getMockForAbstractClass('AssetManager\Config\AbstractConfig');
        $this->mockConfig->expects($this->any())->method('getConfigKey')->will($this->returnValue('cache_control'));

        $asset = new FileAsset(__FILE__);
        $asset->mimetype = 'application/json';

        $mimeResolver = new MimeResolver();

        $this->mockConfig->setAsset($asset);
        $this->mockConfig->setMimeResolver($mimeResolver);
        $this->mockConfig->setPath('img/foo.jpg');

        $this->dummyConfig = array(
            'asset_manager' => array(
                'cache_control' => array(
                    'lifetime' => '5m'
                ),
                'js' => array(
                    'cache_control' => array(
                        'lifetime' => '4m'
                    )
                ),
                'application/json' => array(
                    'cache_control' => array(
                        'lifetime' => '3m'
                    )
                ),
                'img/foo.jpg' => array(
                    'cache_control' => array(
                        'lifetime' => '2m'
                    )
                ),
                'css/bar.min.css' => array(
                    'cache_control' => array(
                        'lifetime' => '1m'
                    )
                ),
            )
        );
    }

    public function testGeneralConfig()
    {

        $this->mockConfig->setConfig($this->dummyConfig);
        $this->mockConfig->enableAssetConfig(false);
        $this->mockConfig->enableMimeConfig(false);
        $this->mockConfig->enableExtensionConfig(false);
        $this->assertEquals(array('lifetime' => '5m'), $this->mockConfig->getConfig());
    }

    public function testExtensionConfig()
    {
        $this->mockConfig->enableAssetConfig(false);
        $this->mockConfig->enableMimeConfig(false);
        $this->mockConfig->getAsset()->mimetype = 'application/javascript';
        $this->mockConfig->setConfig($this->dummyConfig);
        $this->assertEquals(array('lifetime' => '4m'), $this->mockConfig->getConfig());
    }

    public function testAssetConfig()
    {
        $this->mockConfig->setConfig($this->dummyConfig);
        $this->assertEquals(array('lifetime' => '2m'), $this->mockConfig->getConfig());
    }

    public function testMimeConfig()
    {
        $this->mockConfig->enableAssetConfig(false);
        $this->mockConfig->setConfig($this->dummyConfig);
        $this->assertEquals(array('lifetime' => '3m'), $this->mockConfig->getConfig());
    }

    public function testAssetConfigWithRequestGiven()
    {
        $this->mockConfig->getAsset()->mimetype = 'text/css';
        $request = $this->getMock('Zend\Http\Request', array('getBasePath'));
        $request->expects($this->any())->method('getBasePath')->will($this->returnValue(''));

        $request->setUri('/css/foo.min.css');
        $this->mockConfig->setPath($request);

        $this->assertEquals('css/foo.min.css', $this->mockConfig->getPath());
        $this->mockConfig->setConfig($this->dummyConfig);
        $this->assertEquals(array('lifetime' => '5m'), $this->mockConfig->getConfig());

        $request->setUri('/css/bar.min.css');
        $this->mockConfig->setPath($request);

        $this->assertEquals(array('lifetime' => '1m'), $this->mockConfig->getConfig());
    }

    public function testAssetConfigWithRequestGivenAndBasepath()
    {
        $this->mockConfig->getAsset()->mimetype = 'text/css';
        $request = $this->getMock('Zend\Http\Request', array('getBasePath'));
        $request->expects($this->any())->method('getBasePath')->will($this->returnValue('test'));

        $request->setUri('/test/css/bar.min.css');
        $this->mockConfig->setPath($request);

        $this->assertEquals('css/bar.min.css', $this->mockConfig->getPath());
        $this->mockConfig->setConfig($this->dummyConfig);
        $this->assertEquals(array('lifetime' => '1m'), $this->mockConfig->getConfig());
    }
}
