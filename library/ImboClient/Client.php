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
 * @package Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient;

use ImboClient\Driver\DriverInterface,
    ImboClient\Driver\Curl as DefaultDriver,
    ImboClient\ImageUrl\ImageUrl,
    InvalidArgumentException;

/**
 * Client that interacts with the server part of ImboClient
 *
 * This client includes methods that can be used to easily interact with a ImboClient server. All
 * requests made by the client goes through a driver.
 *
 * @package Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */
class Client implements ClientInterface {
    /**
     * The server URL
     *
     * @var string
     */
    private $serverUrl;

    /**
     * Driver used by the client
     *
     * @var ImboClient\Driver\DriverInterface
     */
    private $driver;

    /**
     * Public key used for signed requests
     *
     * @var string
     */
    private $publicKey;

    /**
     * Private key used for signed requests
     *
     * @var string
     */
    private $privateKey;

    /**
     * Class constructor
     *
     * @param string $serverUrl The URL to the ImboClient server, including protocol
     * @param string $publicKey The public key to use
     * @param string $privateKey The private key to use
     * @param ImboClient\Driver\DriverInterface $driver Optional driver to set
     */
    public function __construct($serverUrl, $publicKey, $privateKey, DriverInterface $driver = null) {
        $parts = parse_url($serverUrl);

        // Remove the port from the server url if it's equal to 80
        if (isset($parts['port']) && $parts['port'] == 80) {
            if (empty($parts['path'])) {
                $parts['path'] = '';
            }

            $serverUrl = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
        }

        $this->serverUrl  = rtrim($serverUrl, '/');
        $this->publicKey  = $publicKey;
        $this->privateKey = $privateKey;

        if ($driver === null) {
            // @codeCoverageIgnoreStart
            $driver = new DefaultDriver();
        }
        // @codeCoverageIgnoreEnd

        // Only accept json
        $driver->addRequestHeader('Accept', 'application/json');

        $this->setDriver($driver);
    }

    /**
     * @see ImboClient\ClientInterface::setDriver()
     */
    public function setDriver(DriverInterface $driver) {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @see ImboClient\ClientInterface::getUserUrl()
     */
    public function getUserUrl() {
        return $this->serverUrl . '/users/' . $this->publicKey;
    }

    /**
     * @see ImboClient\ClientInterface::getImagesUrl()
     */
    public function getImagesUrl() {
        return $this->serverUrl . '/users/' . $this->publicKey . '/images';
    }

    /**
     * @see ImboClient\ClientInterface::getImageUrl()
     */
    public function getImageUrl($imageIdentifier, $asString = false) {
        $imageUrl = new ImageUrl($this->serverUrl, $this->publicKey, $imageIdentifier);

        if ($asString) {
            return (string) $imageUrl;
        }

        return $imageUrl;
    }

    /**
     * @see ImboClient\ClientInterface::getMetadataUrl()
     */
    public function getMetadataUrl($imageIdentifier) {
        return $this->getImageUrl($imageIdentifier, true) . '/meta';
    }

    /**
     * Helper method to make sure a local file exists, and that it is not empty
     *
     * @param string $path The path to a local file
     * @throws InvalidArgumentException
     */
    private function validateLocalFile($path) {
        if (!is_file($path)) {
            throw new InvalidArgumentException('File does not exist: ' . $path);
        }

        if (!filesize($path)) {
            throw new InvalidArgumentException('File is of zero length: ' . $path);
        }
    }

    /**
     * @see ImboClient\ClientInterface::addImage()
     */
    public function addImage($path) {
        $this->validateLocalFile($path);

        $imageIdentifier = $this->getImageIdentifier($path);
        $imageUrl = $this->getImageUrl($imageIdentifier, true);

        $url = $this->getSignedUrl(DriverInterface::PUT, $imageUrl);

        return $this->driver->put($url, $path);
    }

    /**
     * @see ImboClient\ClientInterface::imageExists()
     */
    public function imageExists($path) {
        $this->validateLocalFile($path);

        $imageIdentifier = $this->getImageIdentifier($path);
        $response = $this->headImage($imageIdentifier);

        return $response->getStatusCode() === 200;
    }

    /**
     * @see ImboClient\ClientInterface::headImage()
     */
    public function headImage($imageIdentifier) {
        $url = $this->getImageUrl($imageIdentifier, true);

        return $this->driver->head($url);
    }

    /**
     * @see ImboClient\ClientInterface::deleteImage()
     */
    public function deleteImage($imageIdentifier) {
        $imageUrl = $this->getImageUrl($imageIdentifier);
        $url = $this->getSignedUrl(DriverInterface::DELETE, $imageUrl);

        return $this->driver->delete($url);
    }

    /**
     * @see ImboClient\ClientInterface::editMetadata()
     */
    public function editMetadata($imageIdentifier, array $metadata) {
        $metadataUrl = $this->getMetadataUrl($imageIdentifier);
        $url = $this->getSignedUrl(DriverInterface::POST, $metadataUrl);

        return $this->driver->post($url, $metadata);
    }

    /**
     * @see ImboClient\ClientInterface::deleteMetadata()
     */
    public function deleteMetadata($imageIdentifier) {
        $metadataUrl = $this->getMetadataUrl($imageIdentifier);
        $url = $this->getSignedUrl(DriverInterface::DELETE, $metadataUrl);

        return $this->driver->delete($url);
    }

    /**
     * @see ImboClient\ClientInterface::getMetadata()
     */
    public function getMetadata($imageIdentifier) {
        $url = $this->getMetadataUrl($imageIdentifier);

        return $this->driver->get($url);
    }

    /**
     * @see ImboClient\ClientInterface::getNumImages()
     */
    public function getNumImages() {
        $url = $this->getUserUrl();
        $response = $this->driver->get($url);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $body = json_decode($response->getBody());

        return $body->numImages;
    }

    /**
     * @see ImboClient\ClientInterface::getImageProperties()
     */
    public function getImageProperties($imageIdentifier) {
        $response = $this->headImage($imageIdentifier);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $headers = $response->getHeaders();

        return array(
            'width'    => (int) $headers->get('x-imbo-originalwidth'),
            'height'   => (int) $headers->get('x-imbo-originalheight'),
            'filesize' => (int) $headers->get('x-imbo-originalfilesize'),
        );
    }

    /**
     * Generate a signature that can be sent to the server
     *
     * @param string $method HTTP method (PUT, POST or DELETE)
     * @param string $url The URL to send a request to
     * @param string $timestamp GMT timestamp
     * @return string
     */
    private function generateSignature($method, $url, $timestamp) {
        $data = $method . '|' . $url . '|' . $this->publicKey . '|' . $timestamp;

        // Generate signature
        $signature = hash_hmac('sha256', $data, $this->privateKey);

        return $signature;
    }

    /**
     * Get a signed url
     *
     * @param string $method HTTP method
     * @param string $url The URL to send a request to
     * @return string Returns a string with the necessary parts for authenticating
     */
    private function getSignedUrl($method, $url) {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $signature = $this->generateSignature($method, $url, $timestamp);

        $url = sprintf('%s?signature=%s&timestamp=%s', $url, rawurlencode($signature), rawurlencode($timestamp));

        return $url;
    }

    /**
     * Generate an image identifier for a given file
     *
     * @param string $path Path to the local image
     * @return string The image identifier to use with the imbo server
     */
    private function getImageIdentifier($path) {
        return md5_file($path);
    }
}
