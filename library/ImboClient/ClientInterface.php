<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient;

use ImboClient\Driver\DriverInterface,
    ImboClient\Url\Images\QueryInterface,
    ImboClient\Exception\InvalidArgumentException,
    ImboClient\Http\Response\ResponseInterface;

/**
 * Interface for the client
 *
 * @package ImboClient\Interfaces
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface ClientInterface {
    /**
     * Return the current server URL's used by the client
     *
     * @return array
     */
    function getServerUrls();

    /**
     * Set the driver
     *
     * @param DriverInterface $driver The driver to set
     * @return ClientInterface
     */
    function setDriver(DriverInterface $driver);

    /**
     * Get the URL to the status resource
     *
     * @return Url\Status
     */
    function getStatusUrl();

    /**
     * Get the URL to the current user
     *
     * @return Url\User
     */
    function getUserUrl();

    /**
     * Get the URL to the images resource of the current user
     *
     * @return Url\Images
     */
    function getImagesUrl();

    /**
     * Get the URL to a specific image
     *
     * @param string $imageIdentifier The image identifier
     * @return Url\Image
     */
    function getImageUrl($imageIdentifier);

    /**
     * Get the URL to the metadata of a specific image
     *
     * @param string $imageIdentifier The image identifier
     * @return Url\Metadata
     */
    function getMetadataUrl($imageIdentifier);

    /**
     * Add a new image to the server
     *
     * @param string $path Path to the local image
     * @throws InvalidArgumentException Throws an exception if the specified file does not exist or
     *                                  is of zero length
     * @return ResponseInterface
     */
    function addImage($path);

    /**
     * Add a new image to the server by using an image in memory and not a local path
     *
     * @param string $image The actual image data to add to the server
     * @throws InvalidArgumentException Throws an exception if the specified image is empty
     * @return ResponseInterface
     */
    function addImageFromString($image);

    /**
     * Add a new image to the server by specifying a URL to an existing image
     *
     * @param Url\Image|string $url URL to the image you want to add
     * @return ResponseInterface
     */
    function addImageFromUrl($url);

    /**
     * Checks if a given image exists on the server already by specifying a local path
     *
     * @param string $path Path to the local image
     * @throws InvalidArgumentException Throws an exception if the specified file does not exist or
     *                                  is of zero length
     * @return boolean
     */
    function imageExists($path);

    /**
     * Checks if a given image exists on the server already by specifying an image identifier
     *
     * @param  string $imageIdentifier The image identifier
     * @return boolean
     */
    function imageIdentifierExists($imageIdentifier);

    /**
     * Request the image using HEAD
     *
     * @param string $imageIdentifier The image identifier
     * @return ResponseInterface
     */
    function headImage($imageIdentifier);

    /**
     * Delete an image from the server
     *
     * @param string $imageIdentifier The image identifier
     * @return ResponseInterface
     */
    function deleteImage($imageIdentifier);

    /**
     * Edit image metadata
     *
     * @param string $imageIdentifier The image identifier
     * @param array $metadata An array of metadata
     * @return ResponseInterface
     */
    function editMetadata($imageIdentifier, array $metadata);

    /**
     * Replace all existing metadata
     *
     * @param string $imageIdentifier The image identifier
     * @param array $metadata An array of metadata
     * @return ResponseInterface
     */
    function replaceMetadata($imageIdentifier, array $metadata);

    /**
     * Delete metadata
     *
     * @param string $imageIdentifier The image identifier
     * @return ResponseInterface
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
     * @return int
     */
    function getNumImages();

    /**
     * Get an array of images currently stored on the server
     *
     * @param QueryInterface $query A query instance
     * @return ImageInterface[] Returns an array with images (can be empty)
     */
    function getImages(QueryInterface $query = null);

    /**
     * Get the binary data of an image stored on the server
     *
     * @param string $imageIdentifier The image identifier
     * @return string
     */
    function getImageData($imageIdentifier);

    /**
     * Get the binary data of an image stored on the server
     *
     * @param Url\ImageInterface $url URL instance for the image you want to retrieve
     * @return string
     */
    function getImageDataFromUrl(Url\ImageInterface $url);

    /**
     * Get properties of an image
     *
     * This method returns an associative array with the following keys:
     *
     * - width: Width of the image in pixels
     * - height: Height of the image in pixels
     * - size: Size of the image in bytes
     * - mimetype: The original mimetype of the image
     * - extension: The original extension of the image
     *
     * @param string $imageIdentifier The image identifier
     * @return array
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

    /**
     * Get the server status
     *
     * @return array Returns an array with the server status
     */
    function getServerStatus();

    /**
     * Get user information
     *
     * @return array Returns an array with the user information
     */
    function getUserInfo();
}
