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
 * Image implementation
 *
 * @package Urls\Images
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
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
     * {@inheritdoc}
     */
    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension() {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddedDate() {
        return $this->addedDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedDate() {
        return $this->updatedDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * {@inheritdoc}
     */
    public function getChecksum() {
        return $this->checksum;
    }

    /**
     * {@inheritdoc}
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
     * Set date when image was added to the server
     *
     * @param string $added
     */
    private function setAddedDate($added) {
        $this->addedDate = DateTime::createFromFormat('D, d M Y H:i:s T', $added);
    }

    /**
     * Set date when image was last updated on the server
     *
     * @param string $updated
     */
    private function setUpdatedDate($updated) {
        $this->updatedDate = DateTime::createFromFormat('D, d M Y H:i:s T', $updated);
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
