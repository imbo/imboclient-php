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
 * @package ImboClient\Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient;

use ImboClient\Driver\DriverInterface,
    ImboClient\Driver\cURL as DefaultDriver,
    ImboClient\Url\Images\ImageInterface,
    ImboClient\Url\Images\Image,
    ImboClient\Url\Images\QueryInterface,
    ImboClient\Exception\InvalidArgumentException,
    ImboClient\Exception\ServerException;

/**
 * Client that interacts with Imbo servers
 *
 * This client includes methods that can be used to easily interact with Imbo servers.
 *
 * @package ImboClient\Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class Client implements ClientInterface {
    /**
     * The server URLs
     *
     * @var array
     */
    private $serverUrls;

    /**
     * Driver used by the client
     *
     * @var DriverInterface
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
     * @param array|string $serverUrls One or more URLs to the Imbo server, including protocol
     * @param string $publicKey The public key to use
     * @param string $privateKey The private key to use
     * @param DriverInterface $driver Optional driver to set
     * @param Version $version A version instance
     */
    public function __construct($serverUrls, $publicKey, $privateKey, DriverInterface $driver = null, Version $version = null) {
        $this->serverUrls = $this->parseUrls($serverUrls);
        $this->publicKey  = $publicKey;
        $this->privateKey = $privateKey;

        if ($driver === null) {
            $driver = new DefaultDriver();
        }

        if ($version === null) {
            $version = new Version();
        }

        $driver->setRequestHeaders(array(
            'Accept' => 'application/json,image/*',
            'User-Agent' => $version->getVersionString(),
        ));

        $this->setDriver($driver);
    }

    /**
     * {@inheritdoc}
     */
    public function getServerUrls() {
        return $this->serverUrls;
    }

    /**
     * {@inheritdoc}
     */
    public function setDriver(DriverInterface $driver) {
        $this->driver = $driver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusUrl() {
        return new Url\Status($this->serverUrls[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserUrl() {
        return new Url\User($this->serverUrls[0], $this->publicKey, $this->privateKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getImagesUrl() {
        return new Url\Images($this->serverUrls[0], $this->publicKey, $this->privateKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageUrl($imageIdentifier) {
        $hostname = $this->getHostForImageIdentifier($imageIdentifier);

        return new Url\Image($hostname, $this->publicKey, $this->privateKey, $imageIdentifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataUrl($imageIdentifier) {
        $hostname = $this->getHostForImageIdentifier($imageIdentifier);

        return new Url\Metadata($hostname, $this->publicKey, $this->privateKey, $imageIdentifier);
    }

    /**
     * {@inheritdoc}
     */
    public function addImage($path) {
        $imageIdentifier = $this->getImageIdentifier($path);
        $imageUrl = $this->getImageUrl($imageIdentifier)->getUrl();

        $url = $this->getSignedUrl(DriverInterface::PUT, $imageUrl);

        return $this->driver->put($url, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function addImageFromString($image) {
        if (empty($image)) {
            throw new InvalidArgumentException('Specified image is empty');
        }

        $imageIdentifier = $this->getImageIdentifierFromString($image);
        $imageUrl = $this->getImageUrl($imageIdentifier)->getUrl();

        $url = $this->getSignedUrl(DriverInterface::PUT, $imageUrl);

        return $this->driver->putData($url, $image);
    }

    /**
     * {@inheritdoc}
     */
    public function addImageFromUrl($url) {
        if ($url instanceof Url\ImageInterface) {
            $url = $url->getUrl();
        }

        $response = $this->driver->get($url);

        return $this->addImageFromString($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    public function imageExists($path) {
        $imageIdentifier = $this->getImageIdentifier($path);
        return $this->imageIdentifierExists($imageIdentifier);
    }

    /**
     * {@inheritdoc}
     */
    public function imageIdentifierExists($imageIdentifier) {
        try {
            $response = $this->headImage($imageIdentifier);
        } catch (ServerException $e) {
            if ($e->getCode() === 404) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function headImage($imageIdentifier) {
        $url = $this->getImageUrl($imageIdentifier)->getUrl();

        return $this->driver->head($url);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteImage($imageIdentifier) {
        $imageUrl = $this->getImageUrl($imageIdentifier)->getUrl();
        $url = $this->getSignedUrl(DriverInterface::DELETE, $imageUrl);

        return $this->driver->delete($url);
    }

    /**
     * {@inheritdoc}
     */
    public function editMetadata($imageIdentifier, array $metadata) {
        $metadataUrl = $this->getMetadataUrl($imageIdentifier)->getUrl();
        $url = $this->getSignedUrl(DriverInterface::POST, $metadataUrl);

        $data = json_encode($metadata);

        return $this->driver->post($url, $data, array(
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($data),
            'Content-MD5' => md5($data),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function replaceMetadata($imageIdentifier, array $metadata) {
        $metadataUrl = $this->getMetadataUrl($imageIdentifier)->getUrl();
        $url = $this->getSignedUrl(DriverInterface::PUT, $metadataUrl);

        $data = json_encode($metadata);

        return $this->driver->putData($url, $data, array(
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($data),
            'Content-MD5' => md5($data),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMetadata($imageIdentifier) {
        $metadataUrl = $this->getMetadataUrl($imageIdentifier)->getUrl();
        $url = $this->getSignedUrl(DriverInterface::DELETE, $metadataUrl);

        return $this->driver->delete($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($imageIdentifier) {
        $url = $this->getMetadataUrl($imageIdentifier)->getUrl();
        $response = $this->driver->get($url);

        $body = json_decode($response->getBody(), true);

        return $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumImages() {
        $url = $this->getUserUrl()->getUrl();
        $response = $this->driver->get($url);

        $body = json_decode($response->getBody());

        return (int) $body->numImages;
    }

    /**
     * {@inheritdoc}
     */
    public function getImages(QueryInterface $query = null) {
        $params = array();

        if ($query) {
            // Retrieve query parameters, reduce array down to non-empty values
            $params = array_filter(array(
                'page'      => $query->page(),
                'limit'     => $query->limit(),
                'metadata'  => $query->returnMetadata(),
                'from'      => $query->from(),
                'to'        => $query->to(),
                'query'     => $query->metadataQuery(),
            ), function($item) {
                return !empty($item);
            });

            // JSON-encode metadata query, if present
            if (isset($params['query'])) {
                $params['query'] = json_encode($params['query']);
            }
        }

        $url = $this->getImagesUrl();

        // Add query params
        foreach ($params as $key => $value) {
            $url->addQueryParam($key, $value);
        }

        // Fetch the response
        $response = $this->driver->get($url->getUrl());

        $images = json_decode($response->getBody(), true);
        $instances = array();

        foreach ($images as $image) {
            $instances[] = new Image($image);
        }

        return $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageData($imageIdentifier) {
        $url = $this->getImageUrl($imageIdentifier);

        return $this->getImageDataFromUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageDataFromUrl(Url\ImageInterface $url) {
        return $this->driver->get($url->getUrl())->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function getImageProperties($imageIdentifier) {
        $response = $this->headImage($imageIdentifier);
        $headers = $response->getHeaders();

        return array(
            'width'    => (int) $headers->get('x-imbo-originalwidth'),
            'height'   => (int) $headers->get('x-imbo-originalheight'),
            'filesize' => (int) $headers->get('x-imbo-originalfilesize'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getImageIdentifier($path) {
        $this->validateLocalFile($path);

        return $this->generateImageIdentifier(
            file_get_contents($path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getImageIdentifierFromString($image) {
        return $this->generateImageIdentifier($image);
    }

    /**
     * {@inheritdoc}
     */
    public function getServerStatus() {
        $url = $this->getStatusUrl()->getUrl();

        try {
            $response = $this->driver->get($url);
        } catch (ServerException $e) {
            if ($e->getCode() === 500) {
                $response = $e->getResponse();
            } else {
                // re-throw same exception
                throw $e;
            }
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Generate an image identifier based on the data of the image
     *
     * @param string $data The actual image data
     * @return string
     */
    private function generateImageIdentifier($data) {
        return md5($data);
    }

    /**
     * Generate a signature that can be sent to the server
     *
     * @param string $method HTTP method (PUT, POST or DELETE)
     * @param string $url The URL to send a request to
     * @param string $timestamp UTC timestamp
     * @return string
     */
    private function generateSignature($method, $url, $timestamp) {
        $data = $method . '|' . $url . '|' . $this->publicKey . '|' . $timestamp;

        // Generate signature
        $signature = hash_hmac('sha256', $data, $this->privateKey);

        return $signature;
    }

    /**
     * Get a signed URL
     *
     * @param string $method HTTP method
     * @param string $url The URL to send a request to
     * @return string Returns a string with the necessary parts for authenticating
     */
    private function getSignedUrl($method, $url) {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $signature = $this->generateSignature($method, $url, $timestamp);

        $url = sprintf(
            '%s%ssignature=%s&timestamp=%s',
            $url,
            (strpos($url, '?') === false ? '?' : '&'),
            rawurlencode($signature),
            rawurlencode($timestamp)
        );

        return $url;
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
     * Get a predictable hostname for the given image identifier
     *
     * @param string $imageIdentifier The image identifier
     * @return string
     */
    private function getHostForImageIdentifier($imageIdentifier) {
        $dec = hexdec($imageIdentifier[0] . $imageIdentifier[1]);

        return $this->serverUrls[$dec % count($this->serverUrls)];
    }

    /**
     * Parse server host URLs and prepare them for usage
     *
     * @param array|string $urls One or more URLs to an Imbo server
     * @return array Returns an array of URLs
     */
    private function parseUrls($urls) {
        $urls = (array) $urls;
        $result = array();
        $counter = 0;

        foreach ($urls as $serverUrl) {
            if (!preg_match('|^https?://|', $serverUrl)) {
                $serverUrl = 'http://' . $serverUrl;
            }

            $parts = parse_url($serverUrl);

            // Remove the port from the server URL if it's equal to 80 when scheme is http, or if
            // it's equal to 443 when the scheme is https
            if (
                isset($parts['port']) && (
                    ($parts['scheme'] === 'http' && $parts['port'] == 80) ||
                    ($parts['scheme'] === 'https' && $parts['port'] == 443)
                )
            ) {
                if (empty($parts['path'])) {
                    $parts['path'] = '';
                }

                $serverUrl = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
            }

            $serverUrl = rtrim($serverUrl, '/');

            if (!isset($result[$serverUrl])) {
                $result[$serverUrl] = $counter++;
            }
        }

        return array_flip($result);
    }
}
