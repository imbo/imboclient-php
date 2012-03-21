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
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Url\Images;

use DateTime;

/**
 * @package Unittests
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class ImageTest extends \PHPUnit_Framework_TestCase {
    /**
     * Image instance
     *
     * @var ImboClient\Url\Images\Image
     */
    private $image;

    /**
     * Holds an example data set for playing with
     *
     * @var array
     */
    private $data = array(
        'imageIdentifier' => '995b506ba1772e6a3fa25a2e3e618b08',
        'size'            => 655114,
        'extension'       => 'png',
        'mime'            => 'image/png',
        'added'           => 1328559645,
        'width'           => 640,
        'height'          => 480,
        'checksum'        => '995b506ba1772e6a3fa25a2e3e618b08',
        'publicKey'       => 'testsuite',
        'updated'        => 1328559945,
    );

    /**
     * @covers ImboClient\Url\Images\Image::__construct
     * @covers ImboClient\Url\Images\Image::populate
     */
    public function setUp() {
        $this->image = new Image($this->data);
    }

    public function tearDown() {
        $this->image = null;
    }

    /**
     * @covers ImboClient\Url\Images\Image::getIdentifier
     * @covers ImboClient\Url\Images\Image::setIdentifier
     */
    public function testGetIdentifier() {
        $this->assertSame($this->data['imageIdentifier'], $this->image->getIdentifier());
    }

    /**
     * @covers ImboClient\Url\Images\Image::getSize
     * @covers ImboClient\Url\Images\Image::setSize
     */
    public function testGetSize() {
        $this->assertSame($this->data['size'], $this->image->getSize());
    }

    /**
     * @covers ImboClient\Url\Images\Image::getExtension
     * @covers ImboClient\Url\Images\Image::setExtension
     */
    public function testGetExtension() {
        $this->assertSame($this->data['extension'], $this->image->getExtension());
    }

    /**
     * @covers ImboClient\Url\Images\Image::getMimeType
     * @covers ImboClient\Url\Images\Image::setMimeType
     */
    public function testGetMimeType() {
        $this->assertSame($this->data['mime'], $this->image->getMimeType());
    }

    /**
     * @covers ImboClient\Url\Images\Image::getAddedDate
     * @covers ImboClient\Url\Images\Image::setAddedDate
     */
    public function testGetAddedDate() {
        $added = new DateTime('@' . $this->data['added']);
        $this->assertEquals($added, $this->image->getAddedDate());
    }

    /**
     * @covers ImboClient\Url\Images\Image::getUpdatedDate
     * @covers ImboClient\Url\Images\Image::setUpdatedDate
     */
    public function testGetUpdatedDate() {
        $updated = new DateTime('@' . $this->data['updated']);
        $this->assertEquals($updated, $this->image->getUpdatedDate());
    }

    /**
     * @covers ImboClient\Url\Images\Image::getWidth
     * @covers ImboClient\Url\Images\Image::setWidth
     */
    public function testGetWidth() {
        $this->assertSame($this->data['width'], $this->image->getWidth());
    }

    /**
     * @covers ImboClient\Url\Images\Image::getHeight
     * @covers ImboClient\Url\Images\Image::setHeight
     */
    public function testGetHeight() {
        $this->assertSame($this->data['height'], $this->image->getHeight());
    }

    /**
     * @covers ImboClient\Url\Images\Image::getChecksum
     * @covers ImboClient\Url\Images\Image::setChecksum
     */
    public function testChecksum() {
        $this->assertSame($this->data['checksum'], $this->image->getChecksum());
    }

    /**
     * @covers ImboClient\Url\Images\Image::getPublicKey
     * @covers ImboClient\Url\Images\Image::setPublicKey
     */
    public function testGetPublicKey() {
        $this->assertSame($this->data['publicKey'], $this->image->getPublicKey());
    }

}
