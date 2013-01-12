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
 * Interface for an image found in a response to an images query
 *
 * @package ImboClient\Interfaces
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface ImageInterface {
    /**
     * Returns the image identifier for the image
     *
     * @return string
     */
    function getIdentifier();

    /**
     * Returns the size of the image, in bytes
     *
     * @return int
     */
    function getSize();

    /**
     * Returns the original extension for the image
     *
     * @return string
     */
    function getExtension();

    /**
     * Returns the mime type of the image
     *
     * @return string
     */
    function getMimeType();

    /**
     * Returns the date on which the image was added
     *
     * @return DateTime
     */
    function getAddedDate();

    /**
     * Returns the date on which the image was last updated
     *
     * @return DateTime
     */
    function getUpdatedDate();

    /**
     * Returns the width of the image, in pixels
     *
     * @return int
     */
    function getWidth();

    /**
     * Returns the height of the image, in pixels
     *
     * @return int
     */
    function getHeight();

    /**
     * Returns an MD5 checksum of the image data
     *
     * @return string
     */
    function getChecksum();

    /**
     * Returns the public key in which the image is cataloged under
     *
     * @return string
     */
    function getPublicKey();
}
