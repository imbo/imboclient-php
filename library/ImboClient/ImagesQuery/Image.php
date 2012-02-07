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
 * @package Client\ImagesQuery
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient\ImagesQuery;

use DateTime;

/**
 * Image implementation
 *
 * @package Client\ImagesQuery
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */
class Image implements ImageInterface {
    /**
     * Image identifier
     *
     * @var string
     */
    private $identifier;

    /**
     * Size of image, in bytes
     *
     * @var int
     */
    private $size;

    /**
     * Extension of original image
     *
     * @var string
     */
    private $extension;

    /**
     * Mime type of original image
     *
     * @var string
     */
    private $mimeType;

    /**
     * Date which the image was added
     *
     * @var DateTime
     */
    private $addedDate;

    /**
     * Date which the image was last updated
     *
     * @var DateTime
     */
    private $updatedDate;

    /**
     * Width of the image, in pixels
     *
     * @var int
     */
    private $width;

    /**
     * Height of the image, in pixels
     *
     * @var int
     */
    private $height;

    /**
     * MD5 checksum for the original image
     *
     * @var string
     */
    private $checksum;

    /**
     * Public key for this image
     *
     * @var string
     */
    private $publicKey;

    /**
     * Creates a new Image instance from passed data
     *
     * @param array $data Array of data for the given image
     */
    public function __construct(array $data = null) {
        if ($data !== null) {
            $this->populate($data);
        }
    }

    /**
     * Populate this instance
     *
     * @param array $data Data from the response
     */
    private function populate(array $data) {
        $this->setIdentifier($data['imageIdentifier']);
        $this->setSize($data['size']);
        $this->setExtension($data['extension']);
        $this->setMimeType($data['mime']);
        $this->setAddedDate($data['added']);
        $this->setWidth($data['width']);
        $this->setHeight($data['height']);
        $this->setChecksum($data['checksum']);
        $this->setPublicKey($data['publicKey']);
        $this->setUpdatedDate($data['updated']);
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getIdentifier()
     */
    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getSize()
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getExtension()
     */
    public function getExtension() {
        return $this->extension;
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getMimeType()
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getAddedDate()
     */
    public function getAddedDate() {
        return $this->addedDate;
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getUpdatedDate()
     */
    public function getUpdatedDate() {
        return $this->updatedDate;
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getWidth()
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getHeight()
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getChecksum()
     */
    public function getChecksum() {
        return $this->checksum;
    }

    /**
     * @see ImboClient\ImagesQuery\ImageInterface::getPublicKey()
     */
    public function getPublicKey() {
        return $this->publicKey;
    }

    /**
     * Set image identifier
     *
     * @param string $identifier
     */
    private function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }

    /**
     * Set image size, in bytes
     *
     * @param integer $size
     */
    private function setSize($size) {
        $this->size = (int) $size;
    }

    /**
     * Set image extension
     *
     * @param string $extension
     */
    private function setExtension($extension) {
        $this->extension = $extension;
    }

    /**
     * Set image mime type
     *
     * @param string $mime
     */
    private function setMimeType($mime) {
        $this->mimeType = $mime;
    }

    /**
     * Set date when image was added to the server, as unix timestamp
     *
     * @param int $added
     */
    private function setAddedDate($added) {
        $this->addedDate = new DateTime('@' . (int) $added);
    }

    /**
     * Set date when image was last updated on the server, as unix timestamp
     *
     * @param int $updated
     */
    private function setUpdatedDate($updated) {
        $this->updatedDate = new DateTime('@' . (int) $updated);
    }

    /**
     * Set width of the image, in pixels
     *
     * @param int $width
     */
    private function setWidth($width) {
        $this->width = (int) $width;
    }

    /**
     * Set height of the image, in pixels
     *
     * @param int $height
     */
    private function setHeight($height) {
        $this->height = (int) $height;
    }

    /**
     * Set checksum of image data
     *
     * @param string $checksum
     */
    private function setChecksum($checksum) {
        $this->checksum = $checksum;
    }

    /**
     * Set public key the image is catalogued under
     *
     * @param string $publicKey
     */
    private function setPublicKey($publicKey) {
        $this->publicKey = $publicKey;
    }
}
