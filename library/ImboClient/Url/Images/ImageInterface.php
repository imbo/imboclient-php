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
 * @package Interfaces
 * @subpackage Url
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Url\Images;

/**
 * Interface for an image found in a response to an images query
 *
 * @package Interfaces
 * @subpackage Url
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
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
     * Returns the date which the image which added
     *
     * @return DateTime
     */
    function getAddedDate();

    /**
     * Returns the date which the image was last updated
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
     * Returns the public key in which the image is catalogued under
     *
     * @return string
     */
    function getPublicKey();
}
