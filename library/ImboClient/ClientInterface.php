<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
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
 * @package ImboClient
 * @subpackage Interfaces
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient;

use ImboClient\Client\Driver\DriverInterface;

/**
 * Interface for the client
 *
 * @package ImboClient
 * @subpackage Interfaces
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */
interface ClientInterface {
    /**
     * Set the driver
     *
     * @param ImboClient\Client\Driver\DriverInterface $driver The driver to set
     * @return ImboClient\ClientInterface
     */
    function setDriver(DriverInterface $driver);

    /**
     * Get the url to the current user
     *
     * @return string
     */
    function getUserUrl();

    /**
     * Get the url to the images resource of the current user
     *
     * @return string
     */
    function getImagesUrl();

    /**
     * Fetch an ImboClient\ImageUrl\ImageUrlInterface instance
     *
     * @param string $imageIdentifier The image identifier
     * @param boolean $asString Set this to false to get the url as a string
     * @return ImboClient\ImageUrl\ImageUrlInterface|string
     */
    function getImageUrl($imageIdentifier, $asString = false);

    /**
     * Fetch the metadata url to a given image
     *
     * @param string $imageIdentifier The image identifier
     * @return string
     */
    function getMetadataUrl($imageIdentifier);

    /**
     * Add a new image to the server
     *
     * @param string $path Path to the local image
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function addImage($path);

    /**
     * Checks if a given image exists on the server already
     *
     * @param string $path Path to the local image
     * @return boolean
     */
    function imageExists($path);

    /**
     * Request the image using HEAD
     *
     * @param string $imageIdentifier The image identifier
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function headImage($imageIdentifier);

    /**
     * Delete an image from the server
     *
     * @param string $imageIdentifier The image identifier
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function deleteImage($imageIdentifier);

    /**
     * Edit image metadata
     *
     * @param string $imageIdentifier The image identifier
     * @param array $metadata An array of metadata
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function editMetadata($imageIdentifier, array $metadata);

    /**
     * Delete metadata
     *
     * @param string $imageIdentifier The image identifier
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function deleteMetadata($imageIdentifier);

    /**
     * Get image metadata
     *
     * @param string $imageIdentifier The image identifier
     * @return array Returns an array with metadata
     */
    function getMetadata($imageIdentifier);

    /**
     * Get the number of images currently stored at the server
     *
     * If the server responds with an error, this method must return null.
     *
     * @return int|null
     */
    function getNumImages();
}
