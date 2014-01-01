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

use ImboClient\Http,
    Guzzle\Common\Collection,
    Guzzle\Service\Client,
    Guzzle\Service\Description\ServiceDescription,
    Guzzle\Http\Url as GuzzleUrl,
    Guzzle\Common\Exception\GuzzleException,
    InvalidArgumentException;

/**
 * Client that interacts with Imbo servers
 *
 * This client includes methods that can be used to easily interact with Imbo servers.
 *
 * @package Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ImboClient extends Client {
    /**
     * URLs to the Imbo server
     *
     * @var array
     */
    private $serverUrls = array();

    /**
     * The name of the current command
     *
     * This property is used with error handling
     *
     * @var string
     */
    private $currentCommand;

    /**
     * Class constructor
     *
     * Call parent constructor and attach an event listener that in turn will attach listeners to
     * the request based on the command being called.
     *
     * @param string $baseUrl The base URL to Imbo
     * @param array|Collection $config Client configuration
     */
    public function __construct($baseUrl, $config) {
        parent::__construct($baseUrl, $config);

        // Attach event listeners that handles the signing of write operations and the appending of
        // access tokens to requests that require this
        $dispatcher = $this->getEventDispatcher();
        $dispatcher->addSubscriber(new EventSubscriber\AccessToken());
        $dispatcher->addSubscriber(new EventSubscriber\Authenticate());
        $dispatcher->addListener('command.before_send', function($event) {
            $this->currentCommand = $event['command']->getName();
        });
        $dispatcher->addListener('request.error', function($event) {
            if ($this->currentCommand === 'GetServerStatus') {
                // Stop propagation of the event when there is an error with the server status
                $event->stopPropagation();
                $this->currentCommand = null;
            }
        });
    }

    /**
     * Set the server URL's
     *
     * @param array $urls The URL's to the Imbo server
     * @return self
     */
    public function setServerUrls(array $urls) {
        $this->serverUrls = $this->parseUrls($urls);

        return $this;
    }

    /**
     * Client factory
     *
     * @param array $config Client configuration
     * @return ImboClient
     */
    public static function factory($config = array()) {
        $default = array(
            'serverUrls' => null,
            'publicKey' => null,
            'privateKey' => null,
        );

        $required = array('serverUrls', 'publicKey', 'privateKey');
        $config = Collection::fromConfig($config, $default, $required);

        // Create the client and attach the service description
        $description = ServiceDescription::factory(__DIR__ . '/service.php');
        $client = new self($config->get('serverUrls')[0], $config);
        $client->setServerUrls($config->get('serverUrls'));
        $client->setDescription($description);

        return $client;
    }

    /**
     * Add an image from a path
     *
     * @param string $path A path to an image
     * @throws InvalidArgumentException
     */
    public function addImage($path) {
        if (!is_file($path)) {
            throw new InvalidArgumentException('File does not exist: ' . $path);
        }

        if (!filesize($path)) {
            throw new InvalidArgumentException('File is of zero length: ' . $path);
        }

        return $this->addImageFromString(file_get_contents($path));
    }

    /**
     * Add an image from a URL
     *
     * @param GuzzleUrl|string $url A URL to an image
     */
    public function addImageFromUrl($url) {
        if (is_string($url)) {
            // URL specified as a string. Create a URL instance
            $urlInstance = Url::factory($url);
        } else if (!($url instanceof GuzzleUrl)) {
            // Invalid argument
            throw new InvalidArgumentException(
                'Parameter must be a string or an instance of Guzzle\Http\Url'
            );
        } else {
            // Instance of a Guzzle URL
            $urlInstance = $url;
        }

        if (!$urlInstance->getScheme()) {
            throw new InvalidArgumentException('URL is missing scheme: ' . $url);
        }

        // Fetch the image we want to add
        try {
            $image = (string) $this->get($urlInstance)->send()->getBody();
        } catch (GuzzleException $e) {
            throw new InvalidArgumentException('Could not fetch image: ' . $url);
        }

        return $this->addImageFromString($image);
    }

    /**
     * Add an image from memory
     *
     * @param string $image An image in memory
     * @throws InvalidArgumentException
     */
    public function addImageFromString($image) {
        if (empty($image)) {
            throw new InvalidArgumentException('Specified image is empty');
        }

        return $this->getCommand('AddImage', array(
            'publicKey' => $this->getConfig('publicKey'),
            'image' => $image,
        ))->execute();
    }

    /**
     * Fetch the user info of the current user
     */
    public function getUserInfo() {
        return $this->getCommand('GetUserInfo', array(
            'publicKey' => $this->getConfig('publicKey'),
        ))->execute();
    }

    /**
     * Delete an image
     *
     * @param string $imageIdentifier The identifier of the image we want to delete
     */
    public function deleteImage($imageIdentifier) {
        return $this->getCommand('DeleteImage', array(
            'publicKey' => $this->getConfig('publicKey'),
            'imageIdentifier' => $imageIdentifier,
        ))->execute();
    }

    /**
     * Get properties about an image stored in Imbo
     *
     * @param string $imageIdentifier The identifier of the image we want properties about
     */
    public function getImageProperties($imageIdentifier) {
        return $this->getCommand('GetImageProperties', array(
            'publicKey' => $this->getConfig('publicKey'),
            'imageIdentifier' => $imageIdentifier,
        ))->execute();
    }

    /**
     * Edit metadata of an image
     *
     * @param string $imageIdentifier The identifier of the image
     * @param array $metadata The metadata to set
     */
    public function editMetadata($imageIdentifier, array $metadata) {
        return $this->getCommand('EditMetadata', array(
            'publicKey' => $this->getConfig('publicKey'),
            'imageIdentifier' => $imageIdentifier,
            'metadata' => json_encode($metadata),
        ))->execute();
    }

    /**
     * Replace metadata of an image
     *
     * @param string $imageIdentifier The identifier of the image
     * @param array $metadata The metadata to set
     */
    public function replaceMetadata($imageIdentifier, array $metadata) {
        return $this->getCommand('ReplaceMetadata', array(
            'publicKey' => $this->getConfig('publicKey'),
            'imageIdentifier' => $imageIdentifier,
            'metadata' => json_encode($metadata),
        ))->execute();
    }

    /**
     * Get metadata attached to an image
     *
     * @param string $imageIdentifier The identifier of the image
     * @return array
     */
    public function getMetadata($imageIdentifier) {
        return $this->getCommand('GetMetadata', array(
            'publicKey' => $this->getConfig('publicKey'),
            'imageIdentifier' => $imageIdentifier,
        ))->execute();
    }

    /**
     * Get images owned by a user
     *
     * @param ImagesQuery $query A query object
     * @return array
     */
    public function getImages(ImagesQuery $query) {
        $params = array(
            'publicKey' => $this->getConfig('publicKey'),
            'page' => $query->page(),
            'limit' => $query->limit(),
        );

        if ($query->metadata()) {
            $params['metadata'] = true;
        }

        if ($from = $query->from()) {
            $params['from'] = $from;
        }

        if ($to = $query->to()) {
            $params['to'] = $to;
        }

        if ($fields = $query->fields()) {
            $params['fields'] = implode(',', $fields);
        }

        if ($sort = $query->sort()) {
            $params['sort'] = implode(',', $sort);
        }

        return $this->getCommand('GetImages', $params)->execute();
    }

    /**
     * Delete metadata from an image
     *
     * @param string $imageIdentifier The identifier of the image
     * @return array
     */
    public function deleteMetadata($imageIdentifier) {
        return $this->getCommand('DeleteMetadata', array(
            'publicKey' => $this->getConfig('publicKey'),
            'imageIdentifier' => $imageIdentifier,
        ))->execute();
    }

    /**
     * Get all server URL's
     *
     * @return string[]
     */
    public function getServerUrls() {
        return $this->serverUrls;
    }

    /**
     * Get a URL for the status endpoint
     *
     * @return Http\StatusUrl
     */
    public function getStatusUrl() {
        return Http\StatusUrl::factory($this->serverUrls[0] . '/status.json');
    }

    /**
     * Get a URL for the stats endpoint
     *
     * @return Http\StatsUrl
     */
    public function getStatsUrl() {
        return Http\StatsUrl::factory($this->serverUrls[0] . '/stats.json');
    }

    /**
     * Get a URL for the user endpoint
     *
     * @return Http\UserUrl
     */
    public function getUserUrl() {
        $url = sprintf(
            $this->serverUrls[0] . '/users/%s.json',
            $this->getConfig('publicKey')
        );

        return Http\UserUrl::factory($url, $this->getConfig('privateKey'));
    }

    /**
     * Get a URL for the images resource
     *
     * @return Http\ImagesUrl
     */
    public function getImagesUrl() {
        $url = sprintf(
            $this->serverUrls[0] . '/users/%s/images.json',
            $this->getConfig('publicKey')
        );

        return Http\ImagesUrl::factory($url, $this->getConfig('privateKey'));
    }

    /**
     * Get a URL for the image resource
     *
     * @return Http\ImageUrl
     */
    public function getImageUrl($imageIdentifier) {
        $url = sprintf(
            $this->getHostForImageIdentifier($imageIdentifier) . '/users/%s/images/%s',
            $this->getConfig('publicKey'),
            $imageIdentifier
        );

        return Http\ImageUrl::factory($url, $this->getConfig('privateKey'));
    }

    /**
     * Get a URL for the metadata resource
     *
     * @return Http\MetadataUrl
     */
    public function getMetadataUrl($imageIdentifier) {
        $url = sprintf(
            $this->getHostForImageIdentifier($imageIdentifier) . '/users/%s/images/%s/metadata.json',
            $this->getConfig('publicKey'),
            $imageIdentifier
        );

        return Http\MetadataUrl::factory($url, $this->getConfig('privateKey'));
    }

    /**
     * Get the short URL of an image (with optional transformations)
     *
     * @param Http\ImageUrl $imageUrl An instance of an imageUrl
     * @param boolean $asString Set to true to return the URL as a string
     * @return GuzzleUrl|string
     * @throws InvalidArgumentException
     */
    public function getShortUrl(Http\ImageUrl $imageUrl, $asString = false) {
        try {
            $shortUrl = (string) $this->head((string) $imageUrl)->send()->getHeader('x-imbo-shorturl');

            if (!$asString) {
                $shortUrl = GuzzleUrl::factory($shortUrl);
            }
        } catch (GuzzleException $e) {
            throw new InvalidArgumentException('Could not fetch image properties for image: ' . $imageUrl);
        }

        return $shortUrl;
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
     * Get a predictable hostname for the given image identifier
     *
     * @param string $imageIdentifier The image identifier
     * @return string
     */
    private function getUrlForImageIdentifier($imageIdentifier) {
        $dec = hexdec($imageIdentifier[0] . $imageIdentifier[1]);

        return $this->serverUrls[$dec % count($this->serverUrls)];
    }

    /**
     * Parse server URLs and prepare them for usage
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
