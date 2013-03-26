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
        $this->mockConfig->expects($this->any())->method('getRequiredKeys')->will($this->returnValue(array()));

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

    public function testValueInheritance()
    {
        $conf = array(
            'asset_manager' => array(
                'cache_control' => array(
                    'lifetime' => '5m',
                    'foo' => '1',
                    'bar' => '2',
                    'blubb' => '3',
                    'baz' => '4',
                    'quo' => '5'
                ),
                'js' => array(
                    'cache_control' => array(
                        'bar' => '1'
                    )
                ),
                'application/javascript' => array(
                    'cache_control' => array(
                        'bar' => '3',
                        'quo' => '1'
                    )
                ),
                'foo/bar.js' => array(
                    'cache_control' => array(
                        'lifetime' => '10m'
                    )
                )
            )
        );

        #$this->mockConfig->getAsset()->mimetype = 'application/javascript';
        $asset = new FileAsset(__FILE__);
        $asset->mimetype = 'application/javascript';
        $this->mockConfig->setAsset($asset);
        $this->mockConfig->setPath('foo/bar.js');
        $this->mockConfig->setConfig($conf);

        $this->assertEquals(
            array(
                'lifetime' => '10m',
                'bar' => '3',
                'quo' => '1',
                'baz' => '4',
                'blubb' => '3',
                'foo' => '1'
            ), $this->mockConfig->getConfig()
        );

        $this->mockConfig->setPath('foo/blubb.js');
        $this->assertEquals(
            array(
                'lifetime' => '5m',
                'bar' => '3',
                'quo' => '1',
                'baz' => '4',
                'blubb' => '3',
                'foo' => '1'
            ), $this->mockConfig->getConfig()
        );

        $this->mockConfig->enableMimeConfig(false);

        $this->assertEquals(
            array(
                'lifetime' => '5m',
                'bar' => '1',
                'quo' => '5',
                'baz' => '4',
                'blubb' => '3',
                'foo' => '1'
            ), $this->mockConfig->getConfig()
        );

        $this->assertSame(6, count($this->mockConfig));
        $this->assertSame(6, count($this->mockConfig->getIterator()));
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Asset and MimeResolver need to be set for the MimeConfig
     */
    public function testMimeConfigWithoutResolver()
    {
        $mockConfig = $this->getMockForAbstractClass('AssetManager\Config\AbstractConfig');
        $mockConfig->expects($this->any())->method('getConfigKey')->will($this->returnValue('cache_control'));
        $mockConfig->expects($this->any())->method('getRequiredKeys')->will($this->returnValue(array()));
        $mockConfig->enableMimeConfig(true);
        $mockConfig->enableExtensionConfig(false);
        $mockConfig->enableAssetConfig(false);
        $mockConfig->enableGeneralConfig(false);

        $asset = new FileAsset(__FILE__);
        $asset->mimetype = 'application/json';

        $mockConfig->setAsset($asset);
        $mockConfig->setPath('img/foo.jpg');

        $mockConfig->setConfig($this->dummyConfig);
        $mockConfig->getConfig();
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Asset and MimeResolver need to be set for the MimeConfig
     */
    public function testMimeConfigWithoutAsset()
    {
        $mockConfig = $this->getMockForAbstractClass('AssetManager\Config\AbstractConfig');
        $mockConfig->expects($this->any())->method('getConfigKey')->will($this->returnValue('cache_control'));
        $mockConfig->expects($this->any())->method('getRequiredKeys')->will($this->returnValue(array()));
        $mockConfig->enableMimeConfig(true);
        $mockConfig->enableExtensionConfig(false);
        $mockConfig->enableAssetConfig(false);
        $mockConfig->enableGeneralConfig(false);

        $mimeResolver = new MimeResolver();

        $mockConfig->setPath('img/foo.jpg');
        $mockConfig->setMimeResolver($mimeResolver);

        $mockConfig->setConfig($this->dummyConfig);
        $mockConfig->getConfig();
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Asset and MimeResolver need to be set for the ExtensionConfig
     */
    public function testExtensionConfigWithoutAsset()
    {
        $mockConfig = $this->getMockForAbstractClass('AssetManager\Config\AbstractConfig');
        $mockConfig->expects($this->any())->method('getConfigKey')->will($this->returnValue('cache_control'));
        $mockConfig->expects($this->any())->method('getRequiredKeys')->will($this->returnValue(array()));
        $mockConfig->enableMimeConfig(false);
        $mockConfig->enableExtensionConfig(true);
        $mockConfig->enableAssetConfig(false);
        $mockConfig->enableGeneralConfig(false);

        $mimeResolver = new MimeResolver();

        $mockConfig->setPath('img/foo.jpg');
        $mockConfig->setMimeResolver($mimeResolver);

        $mockConfig->setConfig($this->dummyConfig);
        $mockConfig->getConfig();
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Asset and MimeResolver need to be set for the ExtensionConfig
     */
    public function testExtensionConfigWithoutResolver()
    {
        $mockConfig = $this->getMockForAbstractClass('AssetManager\Config\AbstractConfig');
        $mockConfig->expects($this->any())->method('getConfigKey')->will($this->returnValue('cache_control'));
        $mockConfig->expects($this->any())->method('getRequiredKeys')->will($this->returnValue(array()));
        $mockConfig->enableMimeConfig(false);
        $mockConfig->enableExtensionConfig(true);
        $mockConfig->enableAssetConfig(false);
        $mockConfig->enableGeneralConfig(false);

        $asset = new FileAsset(__FILE__);
        $asset->mimetype = 'application/json';

        $mockConfig->setPath('img/foo.jpg');
        $mockConfig->setAsset($asset);

        $mockConfig->setConfig($this->dummyConfig);
        $mockConfig->getConfig();
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Path/Request need to be set for the AssetConfig
     */
    public function testAssetConfigWithoutPath()
    {
        $mockConfig = $this->getMockForAbstractClass('AssetManager\Config\AbstractConfig');
        $mockConfig->expects($this->any())->method('getConfigKey')->will($this->returnValue('cache_control'));
        $mockConfig->expects($this->any())->method('getRequiredKeys')->will($this->returnValue(array()));
        $mockConfig->enableMimeConfig(false);
        $mockConfig->enableExtensionConfig(false);
        $mockConfig->enableAssetConfig(true);
        $mockConfig->enableGeneralConfig(false);

        $mockConfig->setConfig($this->dummyConfig);
        $mockConfig->getConfig();
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Only strings or Zend\Http\Request is allowed
     */
    public function testSetPathWithInvalidArgument()
    {
        $this->mockConfig->setPath(new self());
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Config key 'lifetime' is missing in the config
     */
    public function testConfigWithMissingKey()
    {
        $mockConfig = $this->getMockForAbstractClass('AssetManager\Config\AbstractConfig');
        $mockConfig->expects($this->any())->method('getConfigKey')->will($this->returnValue('cache_control'));
        $mockConfig->expects($this->any())->method('getRequiredKeys')->will($this->returnValue(array('lifetime')));

        $asset = new FileAsset(__FILE__);
        $asset->mimetype = 'application/json';

        $mimeResolver = new MimeResolver();

        $mockConfig->setAsset($asset);
        $mockConfig->setMimeResolver($mimeResolver);
        $mockConfig->setPath('img/foo.jpg');

        $config = array(
            'asset_manager' => array(
                'cache_control' => array(
                    'foo' => 'bar'
                )
            )
        );

        $mockConfig->setConfig($config);
        $mockConfig->getConfig();
    }
}
