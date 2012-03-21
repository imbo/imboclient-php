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
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Url;

/**
 * Custom Interface for image URL's
 *
 * @package Interfaces
 * @subpackage Url
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
interface ImageInterface extends UrlInterface {
    /**
     * Append a query that triggers a border transformation
     *
     * @param string $color Color of the border
     * @param int $width Width of the left and right sides of the border
     * @param int $height Height of the top and bottom parts of the border
     * @return ImboClient\Url\ImageInterface
     */
    function border($color = '000000', $width = 1, $height = 1);

    /**
     * Append a query that triggers a compress transformation
     *
     * @param int $quality A value between 0 and 100 where 100 is the best
     * @return ImboClient\Url\ImageInterface
     */
    function compress($quality = 75);

    /**
     * Change the URL to trigger the convert transformation
     *
     * @param string $type The type to convert to
     * @return ImboClient\Url\ImageInterface
     */
    function convert($type);

    /**
     * Convenience method to trigger gif conversion
     *
     * @return ImboClient\Url\ImageInterface
     */
    function gif();

    /**
     * Convenience method to trigger jpg conversion
     *
     * @return ImboClient\Url\ImageInterface
     */
    function jpg();

    /**
     * Convenience method to trigger png conversion
     *
     * @return ImboClient\Url\ImageInterface
     */
    function png();

    /**
     * Trigger a crop transformation
     *
     * @param int $x X coordinate of the top left corner of the crop
     * @param int $y Y coordinate of the top left corner of the crop
     * @param int $width Width of the crop
     * @param int $height Height of the crop
     * @return ImboClient\Url\ImageInterface
     */
    function crop($x, $y, $width, $height);

    /**
     * Trigger a flip horizontally transformation
     *
     * @return ImboClient\Url\ImageInterface
     */
    function flipHorizontally();

    /**
     * Trigger a flip vertically transformation
     *
     * @return ImboClient\Url\ImageInterface
     */
    function flipVertically();

    /**
     * Trigger a resize transformation
     *
     * @param int $width Width of the resized image
     * @param int $height Height of the resized image
     * @return ImboClient\Url\ImageInterface
     */
    function resize($width = null, $height = null);

    /**
     * Trigger a maxSize transformation
     *
     * @param int $maxWidth Max width of the resized image
     * @param int $maxHeight Max height of the resized image
     * @return ImboClient\Url\ImageInterface
     */
    function maxSize($maxWidth = null, $maxHeight = null);

    /**
     * Trigger a rotate transformation
     *
     * @param int $angle The angle to rotate
     * @param string $bg Background color of the rotated image
     * @return ImboClient\Url\ImageInterface
     */
    function rotate($angle, $bg = '000000');

    /**
     * Trigger a thumbnail transformation
     *
     * @param int $width Width of the thumbnail
     * @param int $height Height of the thumbnail
     * @param string $fit Fit type. 'outbound' or 'inset'
     * @return ImboClient\Url\ImageInterface
     */
    function thumbnail($width = 50, $height = 50, $fit = 'outbound');

    /**
     * Trigger a canvas transformation
     *
     * @param int $width Width of the new canvas
     * @param int $height Height of the new canvas
     * @param string $mode The placement mode
     * @param int $x X coordinate of the placement of the upper left corner of the existing image
     * @param int $y Y coordinate of the placement of the upper left corner of the existing image
     * @param string $bg Background color of the canvas
     * @return ImboClient\Url\ImageInterface
     */
    function canvas($width, $height, $mode = null, $x = null, $y = null, $bg = null);

    /**
     * Resets the URL - removes all transformations
     *
     * @return ImboClient\Url\ImageInterface
     */
    function reset();
}
