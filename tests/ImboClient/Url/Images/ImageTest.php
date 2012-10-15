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
 * @package ImboClient\TestSuite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Url\Images;

use DateTime;

/**
 * @package ImboClient\TestSuite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class ImageTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Image
     */
    private $image;

    /**
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
        'updated'         => 1328559945,
    );

    /**
     * Set up the image instance
     *
     * @covers ImboClient\Url\Images\Image::__construct
     * @covers ImboClient\Url\Images\Image::populate
     */
    public function setUp() {
        $this->image = new Image($this->data);
    }

    /**
     * Tear down the image instance
     */
    public function tearDown() {
        $this->image = null;
    }

    /**
     * The image instance must be able to fetch the image identifier
     *
     * @covers ImboClient\Url\Images\Image::getIdentifier
     * @covers ImboClient\Url\Images\Image::setIdentifier
     */
    public function testCanGetImageIdentifierAfterBeingPopulatedThroughConstructor() {
        $this->assertSame($this->data['imageIdentifier'], $this->image->getIdentifier());
    }

    /**
     * The image instance must be able to fetch the size
     *
     * @covers ImboClient\Url\Images\Image::getSize
     * @covers ImboClient\Url\Images\Image::setSize
     */
    public function testCanGetSizeAfterBeingPopulatedThroughConstructor() {
        $this->assertSame($this->data['size'], $this->image->getSize());
    }

    /**
     * The image instance must be able to fetch the extension
     *
     * @covers ImboClient\Url\Images\Image::getExtension
     * @covers ImboClient\Url\Images\Image::setExtension
     */
    public function testCanGetExtensionAfterBeingPopulatedThroughConstructor() {
        $this->assertSame($this->data['extension'], $this->image->getExtension());
    }

    /**
     * The image instance must be able to fetch the mime type
     *
     * @covers ImboClient\Url\Images\Image::getMimeType
     * @covers ImboClient\Url\Images\Image::setMimeType
     */
    public function testCanGetMimeTypeAfterBeingPopulatedThroughConstructor() {
        $this->assertSame($this->data['mime'], $this->image->getMimeType());
    }

    /**
     * The image instance must be able to fetch the added date as a DateTime instance
     *
     * @covers ImboClient\Url\Images\Image::getAddedDate
     * @covers ImboClient\Url\Images\Image::setAddedDate
     */
    public function testCanGetAddedDateAsDatetimeInstanceAfterBeingPopulatedThroughConstructorAsUnixTimestamp() {
        $added = new DateTime('@' . $this->data['added']);
        $this->assertEquals($added, $this->image->getAddedDate());
    }

    /**
     * The image instance must be able to fetch the updated date as a DateTime instance
     *
     * @covers ImboClient\Url\Images\Image::getUpdatedDate
     * @covers ImboClient\Url\Images\Image::setUpdatedDate
     */
    public function testCanGetUpdatedDateAsDatetimeInstanceAfterBeingPopulatedThroughConstructorAsUnixTimestamp() {
        $updated = new DateTime('@' . $this->data['updated']);
        $this->assertEquals($updated, $this->image->getUpdatedDate());
    }

    /**
     * The image instance must be able to fetch the width
     *
     * @covers ImboClient\Url\Images\Image::getWidth
     * @covers ImboClient\Url\Images\Image::setWidth
     */
    public function testCanGetWidthAfterBeingPopulatedThroughConstructor() {
        $this->assertSame($this->data['width'], $this->image->getWidth());
    }

    /**
     * The image instance must be able to fetch the height
     *
     * @covers ImboClient\Url\Images\Image::getHeight
     * @covers ImboClient\Url\Images\Image::setHeight
     */
    public function testCanGetHeightAfterBeingPopulatedThroughConstructor() {
        $this->assertSame($this->data['height'], $this->image->getHeight());
    }

    /**
     * The image instance must be able to fetch the checksum
     *
     * @covers ImboClient\Url\Images\Image::getChecksum
     * @covers ImboClient\Url\Images\Image::setChecksum
     */
    public function testCanGetChecksumAfterBeingPopulatedThroughConstructor() {
        $this->assertSame($this->data['checksum'], $this->image->getChecksum());
    }

    /**
     * The image instance must be able to fetch the public key
     *
     * @covers ImboClient\Url\Images\Image::getPublicKey
     * @covers ImboClient\Url\Images\Image::setPublicKey
     */
    public function testCanGetPublicKeyAfterBeingPopulatedThroughConstructor() {
        $this->assertSame($this->data['publicKey'], $this->image->getPublicKey());
    }
}
