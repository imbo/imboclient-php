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
 * @subpackage Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient;

use ImboClient\Client\Driver\DriverInterface;
use ImboClient\Client\Driver\Curl as DefaultDriver;
use ImboClient\ImageUrl\ImageUrl;

/**
 * Client that interacts with the server part of ImboClient
 *
 * This client includes methods that can be used to easily interact with a ImboClient server. All
 * requests made by the client goes through a driver.
 *
 * @package ImboClient
 * @subpackage Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
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
     * @var ImboClient\Client\Driver\DriverInterface
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
     * @param ImboClient\Client\Driver\DriverInterface $driver Optional driver to set
     */
    public function __construct($serverUrl, $publicKey, $privateKey, DriverInterface $driver = null) {
        $this->serverUrl  = rtrim($serverUrl, '/');
        $this->publicKey  = $publicKey;
        $this->privateKey = $privateKey;

        if ($driver === null) {
            // @codeCoverageIgnoreStart
            $driver = new DefaultDriver();
        }
        // @codeCoverageIgnoreEnd

        $this->driver = $driver;
    }

    /**
     * @see ImboClient\ClientInterface::getResourceUrl()
     */
    public function getResourceUrl($resourceIdentifier) {
        return $this->serverUrl . '/users/' . $this->publicKey . '/images/' . $resourceIdentifier;
    }

    /**
     * @see ImboClient\ClientInterface::addImage()
     */
    public function addImage($path) {
        $imageIdentifier = $this->getImageIdentifier($path);

        $url = $this->getSignedResourceUrl(DriverInterface::PUT, $imageIdentifier);

        return $this->driver->put($url, $path);
    }

    /**
     * @see ImboClient\ClientInterface::imageExists()
     */
    public function imageExists($path) {
        $imageIdentifier = $this->getImageIdentifier($path);
        $response = $this->headImage($imageIdentifier);

        return $response->getStatusCode() === 200;
    }

    /**
     * @see ImboClient\ClientInterface::headImage()
     */
    public function headImage($imageIdentifier) {
        $url = $this->getResourceUrl($imageIdentifier);

        return $this->driver->head($url);
    }

    /**
     * @see ImboClient\ClientInterface::deleteImage()
     */
    public function deleteImage($imageIdentifier) {
        $url = $this->getSignedResourceUrl(DriverInterface::DELETE, $imageIdentifier);

        return $this->driver->delete($url);
    }

    /**
     * @see ImboClient\ClientInterface::editMetadata()
     */
    public function editMetadata($imageIdentifier, array $metadata) {
        $url = $this->getSignedResourceUrl(DriverInterface::POST, $imageIdentifier . '/meta');

        return $this->driver->post($url, $metadata);
    }

    /**
     * @see ImboClient\ClientInterface::deleteMetadata()
     */
    public function deleteMetadata($imageIdentifier) {
        $url = $this->getSignedResourceUrl(DriverInterface::DELETE, $imageIdentifier . '/meta');

        return $this->driver->delete($url);
    }

    /**
     * @see ImboClient\ClientInterface::getMetadata()
     */
    public function getMetadata($imageIdentifier) {
        $url = $this->getResourceUrl($imageIdentifier . '/meta');

        return $this->driver->get($url);
    }

    /**
     * @see ImboClient\ClientInterface::getImageUrl()
     */
    public function getImageUrl($imageIdentifier) {
        return new ImageUrl($this->serverUrl, $this->publicKey, $imageIdentifier);
    }

    /**
     * Generate a signature that can be sent to the server
     *
     * @param string $method HTTP method (POST or DELETE)
     * @param string $resourceIdentifier The resource identifier (for instance "<image>/meta")
     * @param string $timestamp GMT timestamp
     * @return string
     */
    private function generateSignature($method, $resourceIdentifier, $timestamp) {
        $data = $method . $resourceIdentifier . $this->publicKey . $timestamp;

        // Generate binary hash key
        $hash = hash_hmac('sha256', $data, $this->privateKey, true);

        // Encode signature for the request
        $signature = base64_encode($hash);

        return $signature;
    }

    /**
     * Get a signed url
     *
     * @param string $method HTTP method
     * @param string $resourceIdentifier The resource identifier (for instance "<image>/meta")
     * @return string Returns a string with the necessary parts for authenticating
     */
    private function getSignedResourceUrl($method, $resourceIdentifier) {
        $timestamp = gmdate('Y-m-d\TH:i\Z');
        $signature = $this->generateSignature($method, $resourceIdentifier, $timestamp);

        $url = $this->getResourceUrl($resourceIdentifier)
             . sprintf('?signature=%s&timestamp=%s', rawurlencode($signature), rawurlencode($timestamp));

        return $url;
    }

    /**
     * Generate an image identifier for a given file
     *
     * @param string $path Path to the local image
     * @return string The image identifier to use with the imbo server
     * @throws ImboClient\Client\Exception
     */
    private function getImageIdentifier($path) {
        if (!is_file($path)) {
            throw new Exception('File does not exist: ' . $path);
        }

        return md5_file($path);
    }
}
