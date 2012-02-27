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
 * @subpackage Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient;

use ImboClient\Driver\DriverInterface,
    ImboClient\ImagesQuery\QueryInterface,
    ImboClient\ImageUrl\ImageUrlInterface;

/**
 * Interface for the client
 *
 * @package Interfaces
 * @subpackage Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
interface ClientInterface {
    /**
     * Set the driver
     *
     * @param ImboClient\Driver\DriverInterface $driver The driver to set
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
     * @throws InvalidArgumentException Throws an exception if the specified file does not exist or
     *                                  is of zero length
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function addImage($path);

    /**
     * Add a new image to the server by using an image in memory and not a local path
     *
     * @param string $image The actual image data to add to the server
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function addImageFromString($image);

    /**
     * Add a new image to the server by specifying a URL to an existing image
     *
     * @param ImboClient\ImageUrl\ImageUrlInterface|string $url URL to the image you want to add
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function addImageFromUrl($url);

    /**
     * Checks if a given image exists on the server already
     *
     * @param string $path Path to the local image
     * @throws InvalidArgumentException Throws an exception if the specified file does not exist or
     *                                  is of zero length
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
     * Get the number of images currently stored on the server
     *
     * If the server responds with an error, this method must return false.
     *
     * @return int|boolean
     */
    function getNumImages();

    /**
     * Get an array of images currently stored on the server
     *
     * If the server responds with an error, this method must return false.
     *
     * @param ImboClient\ImagesQuery\QueryInterface $query A query instance
     * @return ImboClient\ImagesQuery\ImageInterface[]|boolean Returns false on error, and an array
     *                                                         of ImboClient\ImageQuery\ImageInterface
     *                                                         instances on success (can be empty)
     */
    function getImages(QueryInterface $query = null);

    /**
     * Get the binary data of an image stored on the server
     *
     * If the server responds with an error, this method must return false.
     *
     * @param string $imageIdentifier The image identifier
     * @return string|boolean
     */
    function getImageData($imageIdentifier);

    /**
     * Get the binary data of an image stored on the server
     *
     * If the server responds with an error, this method must return false.
     *
     * @param ImboClient\ImageUrl\ImageUrlInterface $url ImageUrl-instance for the image you want
     *                                                   to retrieve
     * @return string|boolean
     */
    function getImageDataFromUrl(ImageUrlInterface $url);

    /**
     * Get properties of an image
     *
     * This method returns an associative array with the following keys:
     *
     * - width: Width of the image in pixels
     * - height: Height of the image in pixels
     * - size: Size of the image in bytes
     *
     * If the image does not exist on the server, this method returns false.
     *
     * @param string $imageIdentifier The image identifier
     * @return array|boolean
     */
    function getImageProperties($imageIdentifier);

    /**
     * Generate an image identifier for a given file
     *
     * @param string $path Path to the local image
     * @return string The image identifier to use with the imbo server
     */
    function getImageIdentifier($path);

    /**
     * Generate an image identifier based on actual image data
     *
     * @param string $image String containing an image
     * @return string The image identifier to use with the imbo server
     */
    function getImageIdentifierFromString($image);
}
