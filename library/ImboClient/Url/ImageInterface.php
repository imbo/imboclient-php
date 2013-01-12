<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Url;

/**
 * Custom Interface for image URL's
 *
 * @package Urls\Image
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface ImageInterface extends UrlInterface {
    /**
     * Append a border transformation query parameter to the URL
     *
     * @param string $color Color of the border
     * @param int $width Width of the left and right sides of the border
     * @param int $height Height of the top and bottom parts of the border
     * @return ImageInterface
     */
    function border($color = '000000', $width = 1, $height = 1);

    /**
     * Append a compress transformation query parameter to the URL
     *
     * @param int $quality A value between 0 and 100 where 100 is the best
     * @return ImageInterface
     */
    function compress($quality = 75);

    /**
     * Change the URL to trigger the convert transformation
     *
     * @param string $type The type to convert to
     * @return ImageInterface
     */
    function convert($type);

    /**
     * Convenience method to trigger gif conversion
     *
     * @return ImageInterface
     */
    function gif();

    /**
     * Convenience method to trigger jpg conversion
     *
     * @return ImageInterface
     */
    function jpg();

    /**
     * Convenience method to trigger png conversion
     *
     * @return ImageInterface
     */
    function png();

    /**
     * Append a crop transformation query parameter to the URL
     *
     * @param int $x X coordinate of the top left corner of the crop
     * @param int $y Y coordinate of the top left corner of the crop
     * @param int $width Width of the crop
     * @param int $height Height of the crop
     * @return ImageInterface
     */
    function crop($x, $y, $width, $height);

    /**
     * Append a flipHorizontally transformation query parameter to the URL
     *
     * @return ImageInterface
     */
    function flipHorizontally();

    /**
     * Append a flipVertically transformation query parameter to the URL
     *
     * @return ImageInterface
     */
    function flipVertically();

    /**
     * Append a resize transformation query parameter to the URL
     *
     * @param int $width Width of the resized image
     * @param int $height Height of the resized image
     * @return ImageInterface
     */
    function resize($width = null, $height = null);

    /**
     * Append a maxSize transformation query parameter to the URL
     *
     * @param int $maxWidth Max width of the resized image
     * @param int $maxHeight Max height of the resized image
     * @return ImageInterface
     */
    function maxSize($maxWidth = null, $maxHeight = null);

    /**
     * Append a rotate transformation query parameter to the URL
     *
     * @param float $angle The angle to rotate
     * @param string $bg Background color of the rotated image
     * @return ImageInterface
     */
    function rotate($angle, $bg = '000000');

    /**
     * Append a thumbnail transformation query parameter to the URL
     *
     * @param int $width Width of the thumbnail
     * @param int $height Height of the thumbnail
     * @param string $fit Fit type. 'outbound' or 'inset'
     * @return ImageInterface
     */
    function thumbnail($width = 50, $height = 50, $fit = 'outbound');

    /**
     * Append a canvas transformation query parameter to the URL
     *
     * @param int $width Width of the new canvas
     * @param int $height Height of the new canvas
     * @param string $mode The placement mode
     * @param int $x X coordinate of the placement of the upper left corner of the existing image
     * @param int $y Y coordinate of the placement of the upper left corner of the existing image
     * @param string $bg Background color of the canvas
     * @return ImageInterface
     */
    function canvas($width, $height, $mode = null, $x = null, $y = null, $bg = null);

    /**
     * Append a transpose transformation query parameter to the URL
     *
     * @return ImageInterface
     */
    function transpose();

    /**
     * Append a transverse transformation query parameter to the URL
     *
     * @return ImageInterface
     */
    function transverse();

    /**
     * Append a desaturate transformation query parameter to the URL
     *
     * @return ImageInterface
     */
    function desaturate();
}
