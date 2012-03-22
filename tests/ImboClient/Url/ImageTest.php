<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Url;

/**
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class ImageTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ImboClient\Url\Image
     */
    private $url;

    /**
     * @var string
     */
    private $baseUrl = 'http://host';

    /**
     * @var string
     */
    private $publicKey = 'key';

    /**
     * @var string
     */
    private $privateKey = '41ebdff96ee9986119a5033f30d9a6c8';

    /**
     * @var string
     */
    private $imageIdentifier = '3aea3926533f3c7b87d5500789aa2a17';

    /**
     * @covers ImboClient\Url\Image::__construct
     */
    public function setUp() {
        $this->url = new Image($this->baseUrl, $this->publicKey, $this->privateKey, $this->imageIdentifier);
    }

    public function tearDown() {
        $this->url = null;
    }

    /**
     * @covers ImboClient\Url\Image::border
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testBorder() {
        $this->assertSame($this->url, $this->url->border());
        $this->assertContains('?t[]=' . urlencode('border:color=000000,width=1,height=1'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::border
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testBorderWithCustomValues() {
        $this->assertSame($this->url, $this->url->border('fff', 2, 3));
        $this->assertContains('?t[]=' . urlencode('border:color=fff,width=2,height=3'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::compress
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCompress() {
        $this->assertSame($this->url, $this->url->compress());
        $this->assertContains('?t[]=' . urlencode('compress:quality=75'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::compress
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCompressWithCustomValues() {
        $this->assertSame($this->url, $this->url->compress(42));
        $this->assertContains('?t[]=' . urlencode('compress:quality=42'), $this->url->getUrl());
    }

    /**
     * Data provider for testConvert()
     *
     * @return array
     */
    public function getExtensions() {
        return array(
            array('jpg'),
            array('png'),
            array('gif'),
        );
    }

    /**
     * @dataProvider getExtensions
     * @covers ImboClient\Url\Image::convert
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testConvert($extension) {
        $this->assertSame($this->url, $this->url->convert($extension));
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.' . $extension, $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::gif
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testGif() {
        $this->assertSame($this->url, $this->url->gif());
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.gif', $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::jpg
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testJpg() {
        $this->assertSame($this->url, $this->url->jpg());
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.jpg', $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::png
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testPng() {
        $this->assertSame($this->url, $this->url->png());
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.png', $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::crop
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCrop() {
        $this->assertSame($this->url, $this->url->crop(1, 2, 3, 4));
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '?t[]=' . urlencode('crop:x=1,y=2,width=3,height=4'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::flipHorizontally
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testFlipHorizontally() {
        $this->assertSame($this->url, $this->url->flipHorizontally());
        $this->assertContains('?t[]=flipHorizontally', $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::flipVertically
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testFlipVertically() {
        $this->assertSame($this->url, $this->url->flipVertically());
        $this->assertContains('?t[]=flipVertically', $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::resize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testResizeWithOnlyWidth() {
        $this->assertSame($this->url, $this->url->resize(100));
        $this->assertContains('?t[]=' . urlencode('resize:width=100'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::resize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testResizeWithOnlyHeight() {
        $this->assertSame($this->url, $this->url->resize(null, 100));
        $this->assertContains('?t[]=' . urlencode('resize:height=100'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::resize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testResize() {
        $this->assertSame($this->url, $this->url->resize(1, 2));
        $this->assertContains('?t[]=' . urlencode('resize:width=1,height=2'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::maxSize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testMaxSizeWithOnlyWidth() {
        $this->assertSame($this->url, $this->url->maxSize(100));
        $this->assertContains('?t[]=' . urlencode('maxSize:width=100'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::maxSize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testMaxSizeWithOnlyHeight() {
        $this->assertSame($this->url, $this->url->maxSize(200, 100)->flipVertically());
        $this->assertContains('?t[]=' . urlencode('maxSize:width=200,height=100') . '&t[]=flipVertically', $this->url->getUrl());
        $this->assertContains('?t%5B%5D=' . urlencode('maxSize:width=200,height=100') . '&amp;t%5B%5D=flipVertically', $this->url->getUrlEncoded());
    }

    /**
     * @covers ImboClient\Url\Image::maxSize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testMaxSize() {
        $this->assertSame($this->url, $this->url->maxSize(1, 2));
        $this->assertContains('?t[]=' . urlencode('maxSize:width=1,height=2'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::rotate
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testRotate() {
        $this->assertSame($this->url, $this->url->rotate(42));
        $this->assertContains('?t[]=' . urlencode('rotate:angle=42,bg=000000'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::rotate
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testRotateWithBg() {
        $this->assertSame($this->url, $this->url->rotate(42, 'fff'));
        $this->assertContains('?t[]=' . urlencode('rotate:angle=42,bg=fff'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::thumbnail
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testThumbnailWithAllParams() {
        $this->assertSame($this->url, $this->url->thumbnail(1, 2, 'inset'));
        $this->assertContains('?t[]=' . urlencode('thumbnail:width=1,height=2,fit=inset'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::canvas
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanvasWithRequiredParams() {
        $this->assertSame($this->url, $this->url->canvas(100, 200));
        $this->assertContains('?t[]=' . urlencode('canvas:width=100,height=200'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::canvas
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanvasWithAllParams() {
        $this->assertSame($this->url, $this->url->canvas(100, 200, 'free', 10, 20, '000'));
        $this->assertContains('?t[]=' . urlencode('canvas:width=100,height=200,mode=free,x=10,y=20,bg=000'), $this->url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::getResourceUrl
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testGetUrlWithTransformations() {
        $this->url->flipHorizontally()->png();
        $url = $this->url->getUrl();
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.png?t[]=flipHorizontally', $url);
    }

    /**
     * @covers ImboClient\Url\Image::getResourceUrl
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testGetUrlWithConvertOnly() {
        $this->url->png();
        $url = $this->url->getUrl();
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.png', $url);
    }

    /**
     * Data provider for testGetUrl()
     *
     * @return array
     */
    public function getUrlData() {
        return array(
            array('http://imbo', 'publicKey', 'image', 'http://imbo/users/publicKey/images/image'),
        );
    }

    /**
     * @dataProvider getUrlData
     * @covers ImboClient\Url\Url::getUrl
     * @covers ImboClient\Url\Image::getResourceUrl
     */
    public function testGetUrl($host, $publicKey, $imageIdentifier, $expected) {
        $url = new Image($host, $publicKey, 'privateKey', $imageIdentifier);
        $this->assertStringStartsWith($expected, $url->getUrl());
        $this->assertRegExp('/accessToken=[a-f0-9]{64}$/', $url->getUrl());
    }

    /**
     * @covers ImboClient\Url\Image::reset
     * @covers ImboClient\Url\Image::gif
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testReset() {
        $this->url->gif();
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.gif?', $this->url->getUrl());
        $this->url->reset();
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '?', $this->url->getUrl());
    }
}
