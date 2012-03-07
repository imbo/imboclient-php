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
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\ImageUrl;

/**
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class ImageUrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * URL instance
     *
     * @var ImboClient\ImageUrl\ImageUrl
     */
    private $url;

    /**
     * Base URL
     *
     * @var string
     */
    private $baseUrl = 'http://host';

    /**
     * Public key to test with
     *
     * @var string
     */
    private $publicKey = 'publicKey';

    /**
     * Private key to test with
     *
     * @var string
     */
    private $privateKey = '691cbdf75221b949a9cd2cc36004becd';

    /**
     * Image identifier to test with
     *
     * @var string
     */
    private $imageIdentifier = '83dfab4b5c2678e5f195ea21c5e6750b';

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::__construct
     */
    public function setUp() {
        // Add trailing slash to baseUrl on purpose
        $this->url = new ImageUrl($this->baseUrl . '/', $this->publicKey, $this->privateKey, $this->imageIdentifier);
    }

    public function tearDown() {
        $this->url = null;
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::border
     */
    public function testBorder() {
        $this->assertSame($this->url, $this->url->border());
        $this->assertContains('?t[]=border:color=000000,width=1,height=1', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::border
     */
    public function testBorderWithCustomValues() {
        $this->assertSame($this->url, $this->url->border('fff', 2, 3));
        $this->assertContains('?t[]=border:color=fff,width=2,height=3', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::compress
     */
    public function testCompress() {
        $this->assertSame($this->url, $this->url->compress());
        $this->assertContains('?t[]=compress:quality=75', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::compress
     */
    public function testCompressWithCustomValues() {
        $this->assertSame($this->url, $this->url->compress(42));
        $this->assertContains('?t[]=compress:quality=42', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    public function getExtensions() {
        return array(
            array('jpg'),
            array('png'),
            array('gif'),
        );
    }

    /**
     * @dataProvider getExtensions
     * @covers ImboClient\ImageUrl\ImageUrl::convert
     */
    public function testConvert($extension) {
        $this->assertSame($this->url, $this->url->convert($extension));
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.' . $extension, (string) $this->url);
        $this->assertRegExp('/\?tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::gif
     */
    public function testGif() {
        $this->assertSame($this->url, $this->url->gif());
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.gif', (string) $this->url);
        $this->assertRegExp('/\?tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::jpg
     */
    public function testJpg() {
        $this->assertSame($this->url, $this->url->jpg());
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.jpg', (string) $this->url);
        $this->assertRegExp('/\?tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::png
     */
    public function testPng() {
        $this->assertSame($this->url, $this->url->png());
        $this->assertStringStartsWith($this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.png', (string) $this->url);
        $this->assertRegExp('/\?tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::crop
     */
    public function testCrop() {
        $this->assertSame($this->url, $this->url->crop(1, 2, 3, 4));
        $this->assertContains('?t[]=crop:x=1,y=2,width=3,height=4', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::flipHorizontally
     */
    public function testFlipHorizontally() {
        $this->assertSame($this->url, $this->url->flipHorizontally());
        $this->assertContains('?t[]=flipHorizontally', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::flipVertically
     */
    public function testFlipVertically() {
        $this->assertSame($this->url, $this->url->flipVertically());
        $this->assertContains('?t[]=flipVertically', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::resize
     */
    public function testResizeWithOnlyWidth() {
        $this->assertSame($this->url, $this->url->resize(100));
        $this->assertContains('?t[]=resize:width=100', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::resize
     */
    public function testResizeWithOnlyHeight() {
        $this->assertSame($this->url, $this->url->resize(null, 100));
        $this->assertContains('?t[]=resize:height=100', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::resize
     */
    public function testResize() {
        $this->assertSame($this->url, $this->url->resize(1, 2));
        $this->assertContains('?t[]=resize:width=1,height=2', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::maxSize
     */
    public function testMaxSizeWithOnlyWidth() {
        $this->assertSame($this->url, $this->url->maxSize(100));
        $this->assertContains('?t[]=maxSize:width=100', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::maxSize
     */
    public function testMaxSizeWithOnlyHeight() {
        $this->assertSame($this->url, $this->url->maxSize(null, 100));
        $this->assertContains('?t[]=maxSize:height=100', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::maxSize
     */
    public function testMaxSize() {
        $this->assertSame($this->url, $this->url->maxSize(1, 2));
        $this->assertContains('?t[]=maxSize:width=1,height=2', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::rotate
     */
    public function testRotate() {
        $this->assertSame($this->url, $this->url->rotate(42));
        $this->assertContains('?t[]=rotate:angle=42,bg=000000', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::rotate
     */
    public function testRotateWithBg() {
        $this->assertSame($this->url, $this->url->rotate(42, 'fff'));
        $this->assertContains('?t[]=rotate:angle=42,bg=fff', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::thumbnail
     */
    public function testThumbnailWithAllParams() {
        $this->assertSame($this->url, $this->url->thumbnail(1, 2, 'inset'));
        $this->assertContains('?t[]=thumbnail:width=1,height=2,fit=inset', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::canvas
     */
    public function testCanvasWithRequiredParams() {
        $this->assertSame($this->url, $this->url->canvas(100, 200));
        $this->assertContains('?t[]=canvas:width=100,height=200', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::canvas
     */
    public function testCanvasWithAllParams() {
        $this->assertSame($this->url, $this->url->canvas(100, 200, 'free', 10, 20, '000'));
        $this->assertContains('?t[]=canvas:width=100,height=200,mode=free,x=10,y=20,bg=000', (string) $this->url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::reset
     */
    public function testResetUrl() {
        $this->url->thumbnail(1, 2, 'inset')->png();
        $this->assertSame($this->url, $this->url->reset());
        $this->assertStringEndsWith($this->imageIdentifier, (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::getUrl
     * @covers ImboClient\ImageUrl\ImageUrl::__toString
     * @covers ImboClient\ImageUrl\ImageUrl::getImageUrl
     * @covers ImboClient\ImageUrl\ImageUrl::getQueryString
     */
    public function testGetUrlWithNoTransformationsAdded() {
        $url = $this->url->getUrl();
        $this->assertSame('http://host/users/' . $this->publicKey . '/images/' . $this->imageIdentifier, $url);
        $this->assertSame($url, (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::getUrl
     * @covers ImboClient\ImageUrl\ImageUrl::__toString
     * @covers ImboClient\ImageUrl\ImageUrl::getImageUrl
     * @covers ImboClient\ImageUrl\ImageUrl::getQueryString
     */
    public function testGetUrlWithTransformations() {
        $this->url->flipHorizontally()->png();
        $url = $this->url->getUrl();
        $this->assertStringStartsWith('http://host/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.png?t[]=flipHorizontally', $url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', $url);
        $this->assertSame($url, (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::getUrl
     * @covers ImboClient\ImageUrl\ImageUrl::__toString
     * @covers ImboClient\ImageUrl\ImageUrl::getImageUrl
     * @covers ImboClient\ImageUrl\ImageUrl::getQueryString
     */
    public function testGetUrlWithConvertOnly() {
        $this->url->png();
        $url = $this->url->getUrl();
        $this->assertStringStartsWith('http://host/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.png', $url);
        $this->assertRegExp('/\?tk=[a-f0-9]{32}$/', $url);
        $this->assertSame($url, (string) $this->url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::getUrlEncoded
     * @covers ImboClient\ImageUrl\ImageUrl::getImageUrl
     * @covers ImboClient\ImageUrl\ImageUrl::getQueryString
     */
    public function testGetUrlEncodedWithNoTransformationsAdded() {
        $url = $this->url->getUrlEncoded();
        $this->assertSame('http://host/users/' . $this->publicKey . '/images/' . $this->imageIdentifier, $url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::getUrlEncoded
     * @covers ImboClient\ImageUrl\ImageUrl::append
     * @covers ImboClient\ImageUrl\ImageUrl::getImageUrl
     * @covers ImboClient\ImageUrl\ImageUrl::getQueryString
     */
    public function testGetUrlEncodedWithMultipleTransformations() {
        $this->url->flipHorizontally()->flipVertically()->png();
        $url = $this->url->getUrlEncoded();
        $this->assertStringStartsWith('http://host/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.png?t%5B%5D=flipHorizontally&amp;t%5B%5D=flipVertically', $url);
        $this->assertRegExp('/tk=[a-f0-9]{32}$/', $url);
    }

    /**
     * @covers ImboClient\ImageUrl\ImageUrl::getUrlEncoded
     * @covers ImboClient\ImageUrl\ImageUrl::getImageUrl
     * @covers ImboClient\ImageUrl\ImageUrl::getQueryString
     */
    public function testGetUrlEncodedWithConvertOnly() {
        $this->url->png();
        $url = $this->url->getUrlEncoded();
        $this->assertStringStartsWith('http://host/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '.png', $url);
        $this->assertRegExp('/\?tk=[a-f0-9]{32}$/', $url);
    }
}
