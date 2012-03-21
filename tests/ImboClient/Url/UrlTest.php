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
class UrlTest extends \PHPUnit_Framework_TestCase {
    private $url;
    private $baseUrl = 'http://imbo';
    private $publicKey = 'key';
    private $privateKey = 'key';
    private $imageIdentifier = 'image';

    /**
     * @covers ImboClient\Url\Url::__construct
     */
    public function setUp() {
        // Use the Image implementation to test
        $this->url = new Image($this->baseUrl, $this->publicKey, $this->privateKey, $this->imageIdentifier);
    }

    public function tearDown() {
        $this->url = null;
    }

    /**
     * @covers ImboClient\Url\Url::__toString
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testMagicToString() {
        $asString = (string) $this->url;
        $this->assertSame($this->url->getUrl(), $asString);
        $this->assertStringStartsWith($this->baseUrl, $asString);
    }

    /**
     * @covers ImboClient\Url\Url::getAccessToken
     * @covers ImboClient\Url\Url::setAccessToken
     */
    public function testSetGetAccessToken() {
        $this->assertInstanceOf('ImboClient\Url\AccessToken', $this->url->getAccessToken());
        $accessToken = $this->getMock('ImboClient\Url\AccessTokenInterface');
        $this->assertSame($this->url, $this->url->setAccessToken($accessToken));
        $this->assertSame($accessToken, $this->url->getAccessToken());
    }

    /**
     * @covers ImboClient\Url\Url::getUrlEncoded
     */
    public function testGetUrlEncoded() {
        // add some transformations
        $this->url->border();
        $this->assertContains('t%5B%5D=border:color=000000,width=1,height=1&amp;accessToken=', $this->url->getUrlEncoded());
    }
}
