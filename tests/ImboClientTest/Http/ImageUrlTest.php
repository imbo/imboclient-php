<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClientTest\Http;

use ImboClient\Http\ImageUrl,
    InvalidArgumentException;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers ImboClient\Http\ImageUrl
 */
class ImageTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ImageUrl
     */
    private $url;

    /**
     * Base URL
     *
     * @var string
     */
    private $baseUrl = 'http://imbo/users/christer/images/image';

    /**
     * Set up the image URL instance
     */
    public function setUp() {
        $this->url = ImageUrl::factory($this->baseUrl);
    }

    /**
     * Tear down the image URL instance
     */
    public function tearDown() {
        $this->url = null;
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getTransformations() {
        return array(
            'autoRotate' => array(
                'autoRotate',
                null,
                'autoRotate',
            ),
            'border' => array(
                'border',
                null,
                'border:color=000000,width=1,height=1,mode=outbound',
            ),
            'canvas' => array(
                'canvas',
                array(100, 200),
                'canvas:width=100,height=200',
            ),
            'canvas with all params' => array(
                'canvas',
                array(100, 200, 'center', 10, 20, 'fff'),
                'canvas:width=100,height=200,mode=center,x=10,y=20,bg=fff',
            ),
            'compress' => array(
                'compress',
                null,
                'compress:level=75',
            ),
            'crop' => array(
                'crop',
                array(1, 2, 3, 4),
                'crop:width=3,height=4,x=1,y=2',
            ),
            'crop with center mode' => array(
                'crop',
                array(null, null, 10, 20, 'center'),
                'crop:width=10,height=20,mode=center'
            ),
            'crop with center-x mode' => array(
                'crop',
                array(null, 2, 10, 20, 'center-x'),
                'crop:width=10,height=20,y=2,mode=center-x'
            ),
            'crop with center-y mode' => array(
                'crop',
                array(2, null, 10, 20, 'center-y'),
                'crop:width=10,height=20,x=2,mode=center-y'
            ),
            'desaturate' => array(
                'desaturate',
                null,
                'desaturate',
            ),
            'flipHorizontally' => array(
                'flipHorizontally',
                null,
                'flipHorizontally',
            ),
            'flipVertically' => array(
                'flipVertically',
                null,
                'flipVertically',
            ),
            'maxSize with width' => array(
                'maxSize',
                array(100),
                'maxSize:width=100',
            ),
            'maxSize with height' => array(
                'maxSize',
                array(null, 100),
                'maxSize:height=100',
            ),
            'maxSize with width and height' => array(
                'maxSize',
                array(200, 100),
                'maxSize:width=200,height=100',
            ),
            'progressive' => array(
                'progressive',
                null,
                'progressive',
            ),
            'resize with width' => array(
                'resize',
                array(100),
                'resize:width=100',
            ),
            'resize with width' => array(
                'resize',
                array(null, 100),
                'resize:height=100',
            ),
            'resize with width and height' => array(
                'resize',
                array(200, 100),
                'resize:width=200,height=100',
            ),
            'rotate' => array(
                'rotate',
                array(75),
                'rotate:angle=75,bg=000000',
            ),
            'sepia' => array(
                'sepia',
                null,
                'sepia:threshold=80',
            ),
            'strip' => array(
                'strip',
                null,
                'strip',
            ),
            'thumbnail' => array(
                'thumbnail',
                null,
                'thumbnail:width=50,height=50,fit=outbound',
            ),
            'transpose' => array(
                'transpose',
                null,
                'transpose',
            ),
            'transverse' => array(
                'transverse',
                null,
                'transverse',
            ),
            'watermark' => array(
                'watermark',
                null,
                'watermark:position=top-left,x=0,y=0',
            ),
            'watermark with all params' => array(
                'watermark',
                array('img', 40, 50, 'center', 1, 2),
                'watermark:position=center,x=1,y=2,img=img,width=40,height=50',
            ),
        );
    }

    /**
     * @dataProvider getTransformations
     */
    public function testCanApplyTransformationsToTheUrl($method, array $args = null, $query) {
        if ($args === null) {
            $this->assertSame($this->url, $this->url->$method());
        } else {
            $this->assertSame($this->url, call_user_func_array(array($this->url, $method), $args));
        }

        $uri = parse_url((string) $this->url);
        $queryString = substr($uri['query'], strpos($uri['query'], '=') + 1);

        $this->assertSame(urlencode($query), $queryString);
        $this->assertSame($this->url, $this->url->reset());
        $this->assertSame($this->baseUrl, (string) $this->url);
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getConvertTransformations() {
        return array(
            'convert' => array(
                'convert',
                array('png'),
                '.png',
            ),
            'gif conversion' => array(
                'gif',
                null,
                '.gif',
            ),
            'jpg conversion' => array(
                'jpg',
                null,
                '.jpg',
            ),
            'png conversion' => array(
                'png',
                null,
                '.png',
            ),
        );
    }

    /**
     * @dataProvider getConvertTransformations
     */
    public function testCanConvertToOtherFileExtensions($method, $args = null, $extension) {
        if ($args === null) {
            $this->assertSame($this->url, $this->url->$method());
        } else {
            $this->assertSame($this->url, call_user_func_array(array($this->url, $method), $args));
        }

        $this->assertStringEndsWith('image' . $extension, (string) $this->url);
        $this->assertSame($this->url, $this->url->reset());
        $this->assertSame($this->baseUrl, (string) $this->url);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage width and height must be specified
     */
    public function testCanvasMethodThrowExceptionOnMissingParameters() {
        $this->url->canvas(100, 0);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage width and/or height must be specified
     */
    public function testMaxSizeMethodThrowExceptionOnMissingParameters() {
        $this->url->maxSize();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage width and/or height must be specified
     */
    public function testResizeMethodThrowExceptionOnMissingParameters() {
        $this->url->resize();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage angle must be specified
     */
    public function testRotateMethodThrowExceptionOnMissingParameters() {
        $this->url->rotate(0);
    }

    public function testCanAddMultipleTransformations() {
        $this->assertSame(
            'http://imbo/users/christer/images/image.jpg?t%5B0%5D=border%3Acolor%3D000000%2Cwidth%3D1%2Cheight%3D1%2Cmode%3Doutbound&t%5B1%5D=desaturate',
            (string) $this->url->border()->jpg()->desaturate()
        );
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getCropParams() {
        return array(
            'no crop mode and missing x and/or y value(s)' => array(
                null, null, 100, 100, null, 'x and y needs to be specified without a crop mode',
            ),
            'center-x mode with missing y parameter' => array(
                null, null, 100, 100, 'center-x', 'y needs to be specified when mode is center-x',
            ),
            'center-y mode with missing x parameter' => array(
                null, null, 100, 100, 'center-y', 'x needs to be specified when mode is center-y',
            ),
            'missing width' => array(
                0, 0, null, 100, null, 'width and height needs to be specified',
            ),
            'missing height' => array(
                0, 0, 100, null, null, 'width and height needs to be specified',
            ),
        );
    }

    /**
     * @dataProvider getCropParams
     */
    public function testValidatesCropParameters($x, $y, $width, $height, $mode, $exceptionMessage) {
        try {
            $this->url->crop($x, $y, $width, $height, $mode);
            $this->fail('Expected an exception');
        } catch (InvalidArgumentException $e) {
            $this->assertSame($exceptionMessage, $e->getMessage());
        }
    }
}
