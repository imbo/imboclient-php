<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Url\Images;

use DateTime;

/**
 * @package Test suite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
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
        'added'           => 'Thu, 15 Nov 2012 16:58:06 GMT',
        'width'           => 640,
        'height'          => 480,
        'checksum'        => '995b506ba1772e6a3fa25a2e3e618b08',
        'publicKey'       => 'testsuite',
        'updated'         => 'Thu, 15 Nov 2012 16:58:06 GMT',
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
    public function testCanGetAddedDateAsDatetimeInstanceAfterBeingPopulatedThroughConstructorAsFormattedString() {
        $added = DateTime::createFromFormat('D, d M Y H:i:s T', $this->data['added']);
        $this->assertEquals($added, $this->image->getAddedDate());
    }

    /**
     * The image instance must be able to fetch the updated date as a DateTime instance
     *
     * @covers ImboClient\Url\Images\Image::getUpdatedDate
     * @covers ImboClient\Url\Images\Image::setUpdatedDate
     */
    public function testCanGetUpdatedDateAsDatetimeInstanceAfterBeingPopulatedThroughConstructorAsFormattedString() {
        $updated = DateTime::createFromFormat('D, d M Y H:i:s T', $this->data['updated']);
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
