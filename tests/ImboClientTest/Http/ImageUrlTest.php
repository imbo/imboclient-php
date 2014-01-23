<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Http;

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
            'autoRotate' => array('autoRotate', null, $this->baseUrl . '?t%5B0%5D=autoRotate'),
            'border' => array('border', null, $this->baseUrl . '?t%5B0%5D=border%3Acolor%3D000000%2Cwidth%3D1%2Cheight%3D1%2Cmode%3Doutbound'),
            'canvas' => array('canvas', array(100, 200), $this->baseUrl . '?t%5B0%5D=canvas%3Awidth%3D100%2Cheight%3D200'),
            'canvas with all params' => array('canvas', array(100, 200, 'center', 10, 20, 'fff'), $this->baseUrl . '?t%5B0%5D=canvas%3Awidth%3D100%2Cheight%3D200%2Cmode%3Dcenter%2Cx%3D10%2Cy%3D20%2Cbg%3Dfff'),
            'compress' => array('compress', null, $this->baseUrl . '?t%5B0%5D=compress%3Alevel%3D75'),
            'convert' => array('convert', array('png'), $this->baseUrl . '.png'),
            'gif conversion' => array('gif', null, $this->baseUrl . '.gif'),
            'jpg conversion' => array('jpg', null, $this->baseUrl . '.jpg'),
            'png conversion' => array('png', null, $this->baseUrl . '.png'),
            'crop' => array('crop', array(1, 2, 3, 4), $this->baseUrl . '?t%5B0%5D=crop%3Ax%3D1%2Cy%3D2%2Cwidth%3D3%2Cheight%3D4'),
            'desaturate' => array('desaturate', null, $this->baseUrl . '?t%5B0%5D=desaturate'),
            'flipHorizontally' => array('flipHorizontally', null, $this->baseUrl . '?t%5B0%5D=flipHorizontally'),
            'flipVertically' => array('flipVertically', null, $this->baseUrl . '?t%5B0%5D=flipVertically'),
            'maxSize with width' => array('maxSize', array(100), $this->baseUrl . '?t%5B0%5D=maxSize%3Awidth%3D100'),
            'maxSize with height' => array('maxSize', array(null, 100), $this->baseUrl . '?t%5B0%5D=maxSize%3Aheight%3D100'),
            'maxSize with width and height' => array('maxSize', array(200, 100), $this->baseUrl . '?t%5B0%5D=maxSize%3Awidth%3D200%2Cheight%3D100'),
            'progressive' => array('progressive', null, $this->baseUrl . '?t%5B0%5D=progressive'),
            'resize with width' => array('resize', array(100), $this->baseUrl . '?t%5B0%5D=resize%3Awidth%3D100'),
            'resize with width' => array('resize', array(null, 100), $this->baseUrl . '?t%5B0%5D=resize%3Aheight%3D100'),
            'resize with width and height' => array('resize', array(200, 100), $this->baseUrl . '?t%5B0%5D=resize%3Awidth%3D200%2Cheight%3D100'),
            'rotate' => array('rotate', array(75), $this->baseUrl . '?t%5B0%5D=rotate%3Aangle%3D75%2Cbg%3D000000'),
            'sepia' => array('sepia', null, $this->baseUrl . '?t%5B0%5D=sepia%3Athreshold%3D80'),
            'strip' => array('strip', null, $this->baseUrl . '?t%5B0%5D=strip'),
            'thumbnail' => array('thumbnail', null, $this->baseUrl . '?t%5B0%5D=thumbnail%3Awidth%3D50%2Cheight%3D50%2Cfit%3Doutbound'),
            'transpose' => array('transpose', null, $this->baseUrl . '?t%5B0%5D=transpose'),
            'transverse' => array('transverse', null, $this->baseUrl . '?t%5B0%5D=transverse'),
            'watermark' => array('watermark', null, $this->baseUrl . '?t%5B0%5D=watermark%3Aposition%3Dtop-left%2Cx%3D0%2Cy%3D0'),
            'watermark with all params' => array('watermark', array('img', 40, 50, 'center', 1, 2), $this->baseUrl . '?t%5B0%5D=watermark%3Aposition%3Dcenter%2Cx%3D1%2Cy%3D2%2Cimg%3Dimg%2Cwidth%3D40%2Cheight%3D50'),
        );
    }

    /**
     * @dataProvider getTransformations
     */
    public function testCanApplyTransformationsToTheUrl($method, array $args = null, $result) {
        if ($args === null) {
            $this->assertSame($this->url, $this->url->$method());
        } else {
            $this->assertSame($this->url, call_user_func_array(array($this->url, $method), $args));
        }

        $this->assertSame($result, (string) $this->url);
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
}
