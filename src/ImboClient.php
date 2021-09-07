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
    ImboClient\Helper\PublicKeyFallback,
    Guzzle\Common\Collection,
    Guzzle\Service\Client as GuzzleClient,
    Guzzle\Service\Description\ServiceDescription,
    Guzzle\Service\Resource\Model,
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
class ImboClient extends GuzzleClient {
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
    public $currentCommand;

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

        if (empty($config['serverUrls'])) {
            $config['serverUrls'] = array($baseUrl);
        }

        $this->setServerUrls($config['serverUrls']);
        $this->setDescription(ServiceDescription::factory(__DIR__ . '/service.php'));
        $this->setUserAgent('ImboClient/' . Version::VERSION, true);

        // Attach event listeners that handles the signing of write operations and the appending of
        // access tokens to requests that require this
        $dispatcher = $this->getEventDispatcher();
        $dispatcher->addSubscriber(new EventSubscriber\AccessToken());
        $dispatcher->addSubscriber(new EventSubscriber\Authenticate());
        $dispatcher->addSubscriber(new EventSubscriber\PublicKey());

        $client = $this;
        $dispatcher->addListener('command.before_send', function($event) use ($client) {
            $client->currentCommand = $event['command']->getName();
        });
        $dispatcher->addListener('request.error', function($event) use ($client) {
            if ($client->currentCommand === 'GetServerStatus') {
                // Stop propagation of the event when there is an error with the server status
                $event->stopPropagation();
                $client->currentCommand = null;
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
     * @throws InvalidArgumentException
     */
    public static function factory($config = array()) {
        $default = array(
            'serverUrls' => null,
            'user' => null,
            'publicKey' => null,
            'privateKey' => null,
        );

        // Backwards-compatibility with old client where user === publicKey
        if (!isset($config['user']) && isset($config['publicKey'])) {
            $config['user'] = $config['publicKey'];
        }

        $required = array('serverUrls', 'publicKey', 'privateKey', 'user');
        $config = Collection::fromConfig($config, $default, $required);

        if (!is_array($serverUrls = $config->get('serverUrls')) || empty($serverUrls)) {
            throw new InvalidArgumentException('serverUrls must be an array');
        }

        // Create the client and attach the service description
        return new self($serverUrls[0], $config);
    }

    /**
     * Get the current public key
     *
     * @return string
     */
    public function getPublicKey() {
        return $this->getConfig('publicKey');
    }

    /**
     * Get the current user
     *
     * @return string
     */
    public function getUser() {
        return $this->getConfig('user');
    }

    /**
     * Set the current user
     *
     * @param string $user
     * @return self
     */
    public function setUser($user) {
        $this->getConfig()->set('user', $user);

        return $this;
    }

    /**
     * Add an image from a path
     *
     * @param string $path A path to an image
     * @return Model
     * @throws InvalidArgumentException
     */
    public function addImage($path) {
        $this->validateLocalFile($path);

        return $this->addImageFromString(file_get_contents($path));
    }

    /**
     * Add an image from a URL
     *
     * @param GuzzleUrl|string $url A URL to an image
     * @return Model
     * @throws InvalidArgumentException
     */
    public function addImageFromUrl($url) {
        if (is_string($url)) {
            // URL specified as a string. Create a URL instance
            $url = GuzzleUrl::factory($url);
        }

        if (!($url instanceof GuzzleUrl)) {
            // Invalid argument
            throw new InvalidArgumentException(
                'Parameter must be a string or an instance of Guzzle\Http\Url'
            );
        }

        if (!$url->getScheme()) {
            throw new InvalidArgumentException('URL is missing scheme: ' . (string) $url);
        }

        // Fetch the image we want to add
        $image = (string) $this->get($url)->send()->getBody();

        return $this->addImageFromString($image);
    }

    /**
     * Add an image from memory
     *
     * @param string $image An image in memory
     * @return Model
     * @throws InvalidArgumentException
     */
    public function addImageFromString($image) {
        if (empty($image)) {
            throw new InvalidArgumentException('Specified image is empty');
        }

        return $this->getCommand('AddImage', array(
            'user' => $this->getUser(),
            'image' => $image,
        ))->execute();
    }

    /**
     * Get the server stats
     *
     * @return array
     */
    public function getServerStats() {
        return $this->getCommand('GetServerStats')->execute();
    }

    /**
     * Get the server status
     *
     * @return Model
     */
    public function getServerStatus() {
        return $this->getCommand('GetServerStatus')->execute();
    }

    /**
     * Fetch the user info of the current user
     *
     * @return Model
     */
    public function getUserInfo() {
        $userInfo = $this->getCommand('GetUserInfo', array(
            'user' => $this->getUser(),
        ))->execute();

        return PublicKeyFallback::fallback($userInfo);
    }

    /**
     * Delete an image
     *
     * @param string $imageIdentifier The identifier of the image we want to delete
     * @return Model
     */
    public function deleteImage($imageIdentifier) {
        return $this->getCommand('DeleteImage', array(
            'user' => $this->getUser(),
            'imageIdentifier' => $imageIdentifier,
        ))->execute();
    }

    /**
     * Get properties about an image stored in Imbo
     *
     * @param string $imageIdentifier The identifier of the image we want properties about
     * @return Model
     */
    public function getImageProperties($imageIdentifier) {
        return $this->getCommand('GetImageProperties', array(
            'user' => $this->getUser(),
            'imageIdentifier' => $imageIdentifier,
        ))->execute();
    }

    /**
     * Edit metadata of an image
     *
     * @param string $imageIdentifier The identifier of the image
     * @param array $metadata The metadata to set
     * @return Model
     */
    public function editMetadata($imageIdentifier, array $metadata) {
        return $this->getCommand('EditMetadata', array(
            'user' => $this->getUser(),
            'imageIdentifier' => $imageIdentifier,
            'metadata' => json_encode($metadata),
        ))->execute();
    }

    /**
     * Replace metadata of an image
     *
     * @param string $imageIdentifier The identifier of the image
     * @param array $metadata The metadata to set
     * @return Model
     */
    public function replaceMetadata($imageIdentifier, array $metadata) {
        return $this->getCommand('ReplaceMetadata', array(
            'user' => $this->getUser(),
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
            'user' => $this->getUser(),
            'imageIdentifier' => $imageIdentifier,
        ))->execute();
    }

    /**
     * Get images owned by a user
     *
     * @param ImagesQuery $query An optional images query object
     * @return Model
     */
    public function getImages(ImagesQuery $query = null) {
        if (!$query) {
            $query = new ImagesQuery();
        }

        $params = array(
            'user' => $this->getUser(),
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
            $params['fields'] = $fields;
        }

        if ($sort = $query->sort()) {
            $params['sort'] = $sort;
        }

        if ($ids = $query->ids()) {
            $params['ids'] = $ids;
        }

        if ($checksums = $query->checksums()) {
            $params['checksums'] = $checksums;
        }

        if ($originalChecksums = $query->originalChecksums()) {
            $params['originalChecksums'] = $originalChecksums;
        }

        $response = $this->getCommand('GetImages', $params)->execute();
        $response['images'] = array_map(
            array('ImboClient\Helper\PublicKeyFallback', 'fallback'),
            $response['images']
        );

        return $response;
    }

    /**
     * Delete metadata from an image
     *
     * @param string $imageIdentifier The identifier of the image
     * @return Model
     */
    public function deleteMetadata($imageIdentifier) {
        return $this->getCommand('DeleteMetadata', array(
            'user' => $this->getUser(),
            'imageIdentifier' => $imageIdentifier,
        ))->execute();
    }

    /**
     * Generate a short URL
     *
     * @param Http\ImageUrl $imageUrl An instance of an imageUrl
     * @return Model
     */
    public function generateShortUrl(Http\ImageUrl $imageUrl) {
        $transformations = $imageUrl->getTransformations();

        if ($transformations) {
            $transformations = '?t[]=' . implode('&t[]=', $transformations);
        } else {
            $transformations = null;
        }

        $params = array(
            'user' => $this->getUser(),
            'imageIdentifier' => $imageUrl->getImageIdentifier(),
            'extension' => $imageUrl->getExtension(),
            'query' => $transformations,
        );

        return $this->getCommand('GenerateShortUrl', array(
            'user' => $this->getUser(),
            'imageIdentifier' => $imageUrl->getImageIdentifier(),
            'params' => json_encode($params),
        ))->execute();
    }

    /**
     * Get the available resource groups
     *
     * @param Query $query An optional query object
     * @return Model
     */
    public function getResourceGroups(Query $query = null) {
        if (!$query) {
            $query = new Query();
        }

        return $this->getCommand('GetResourceGroups', array(
            'page' => $query->page(),
            'limit' => $query->limit(),
        ))->execute();
    }

    /**
     * Get the details of a specific resource group
     *
     * @param string $groupName Name of group to get
     * @return Model
     * @throws InvalidArgumentException If the group name is invalid
     */
    public function getResourceGroup($groupName) {
        $this->validateResourceGroupName($groupName);

        return $this->getCommand('GetResourceGroup', array(
            'groupName' => $groupName,
        ))->execute();
    }

    /**
     * Add a new resource group
     *
     * @param string $groupName Name of the group to create
     * @param array $resources Array of resource names the group should contain
     * @return Model
     * @throws InvalidArgumentException Throw when group name is invalid or group already exists
     */
    public function addResourceGroup($groupName, array $resources) {
        $this->validateResourceGroupName($groupName);

        if ($this->resourceGroupExists($groupName)) {
            throw new InvalidArgumentException(
                'Resource group with name "' . $groupName . '" already exists'
            );
        }

        return $this->editResourceGroup($groupName, $resources);
    }

    /**
     * Edit a resource group
     *
     * Note: If the resource group does not already exist, it will be created
     *
     * @param string $groupName Name of the group to edit
     * @param array $resources Array of resource names the group should contain
     * @return Model
     * @throws InvalidArgumentException Thrown when group name is invalid or group already exists
     */
    public function editResourceGroup($groupName, array $resources) {
        $this->validateResourceGroupName($groupName);

        return $this->getCommand('EditResourceGroup', array(
            'groupName' => $groupName,
            'resources' => json_encode($resources),
        ))->execute();
    }

    /**
     * Delete a resource group
     *
     * @param  string $groupName Name of the group to delete
     * @return Model
     * @throws InvalidArgumentException Thrown when the group name is invalid or does not exist
     */
    public function deleteResourceGroup($groupName) {
        $this->validateResourceGroupName($groupName);

        return $this->getCommand('DeleteResourceGroup', array(
            'groupName' => $groupName,
        ))->execute();
    }

    /**
     * Checks if a given group exists on the server already
     *
     * @param string $groupName Name of the group
     * @throws InvalidArgumentException Throws an exception if group name is invalid
     * @return boolean
     */
    public function resourceGroupExists($groupName) {
        $this->validateResourceGroupName($groupName);

        return $this->resourceExists($this->getResourceGroupUrl($groupName));
    }

    /**
     * Create a new public/private key pair
     *
     * @param string $publicKey Public key to create
     * @param string $privateKey Private key for the new public key
     * @return Model
     * @throws InvalidArgumentException If the public key name is invalid
     */
    public function addPublicKey($publicKey, $privateKey) {
        $this->validatePublicKeyName($publicKey);

        if ($this->publicKeyExists($publicKey)) {
            throw new InvalidArgumentException(
                'Public key with name "' . $publicKey . '" already exists'
            );
        }

        return $this->getCommand('EditPublicKey', array(
            'properties' => json_encode(array('privateKey' => $privateKey)),
            'publicKey' => $publicKey,
        ))->execute();
    }

    /**
     * Edit a public/private key pair
     *
     * @param string $publicKey Public key to alter private key for
     * @param string $privateKey New private key to use for the given public key
     * @return Model
     * @throws InvalidArgumentException If the public key name is invalid
     */
    public function editPublicKey($publicKey, $privateKey) {
        $this->validatePublicKeyName($publicKey);

        return $this->getCommand('EditPublicKey', array(
            'properties' => json_encode(array('privateKey' => $privateKey)),
            'publicKey' => $publicKey,
        ))->execute();
    }

    /**
     * Delete a public key
     *
     * @param  string $publicKey Name of the public key to delete
     * @return Model
     * @throws InvalidArgumentException If the public key name is invalid
     */
    public function deletePublicKey($publicKey) {
        $this->validatePublicKeyName($publicKey);

        return $this->getCommand('DeletePublicKey', array(
            'publicKey' => $publicKey,
        ))->execute();
    }

    /**
     * Checks if a given public key exists on the server already
     *
     * @param string $publicKey Public key
     * @return boolean
     * @throws InvalidArgumentException If the public key name is invalid
     */
    public function publicKeyExists($publicKey) {
        $this->validatePublicKeyName($publicKey);

        return $this->resourceExists($this->getKeyUrl($publicKey));
    }

    /**
     * Get access control rules for the given public key
     *
     * @param string $publicKey Public key
     * @return Model
     * @throws InvalidArgumentException If the public key name is invalid
     */
    public function getAccessControlRules($publicKey) {
        $this->validatePublicKeyName($publicKey);

        return $this->getCommand('GetAccessControlRules', array(
            'publicKey' => $publicKey,
        ))->execute();
    }

    /**
     * Get the access control rule with the given ID which belongs to the given public key
     *
     * @param string $publicKey Public key
     * @param string $ruleId ID of the rule to retrieve
     * @return Model
     * @throws InvalidArgumentException If the public key name is invalid
     */
    public function getAccessControlRule($publicKey, $ruleId) {
        $this->validatePublicKeyName($publicKey);

        return $this->getCommand('GetAccessControlRule', array(
            'publicKey' => $publicKey,
            'id' => $ruleId,
        ))->execute();
    }

    /**
     * Add access control rule to the given public key
     *
     * @param string $publicKey Public key to add rule to
     * @param array $rule Rule to add
     * @return Model
     * @throws InvalidArgumentException If the public key name is invalid
     */
    public function addAccessControlRule($publicKey, $rule) {
        return $this->addAccessControlRules($publicKey, array($rule));
    }

    /**
     * Add access control rules to the given public key
     *
     * @param string $publicKey Public key to add rules to
     * @param array $rules Rules to add
     * @return Model
     * @throws InvalidArgumentException If the public key name is invalid
     */
    public function addAccessControlRules($publicKey, $rules) {
        $this->validatePublicKeyName($publicKey);

        return $this->getCommand('AddAccessControlRules', array(
            'publicKey' => $publicKey,
            'rules' => json_encode($rules),
        ))->execute();
    }

    /**
     * Delete an access control rule
     *
     * @param string $publicKey Name of the public key which owns the rule
     * @param string $ruleId ID of the rule to delete
     * @return Model
     * @throws InvalidArgumentException Thrown if the public key name is invalid
     */
    public function deleteAccessControlRule($publicKey, $ruleId) {
        $this->validatePublicKeyName($publicKey);

        return $this->getCommand('DeleteAccessControlRule', array(
            'publicKey' => $publicKey,
            'id' => $ruleId,
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
        return Http\StatusUrl::factory($this->getBaseUrl() . '/status.json');
    }

    /**
     * Get a URL for the stats endpoint
     *
     * @return Http\StatsUrl
     */
    public function getStatsUrl() {
        return Http\StatsUrl::factory($this->getBaseUrl() . '/stats.json');
    }

    /**
     * Get a URL for the groups endpoint
     *
     * @return Http\GroupsUrl
     */
    public function getResourceGroupsUrl() {
        return Http\ResourceGroupsUrl::factory(
            $this->getBaseUrl() . '/groups.json',
            $this->getConfig('privateKey'),
            $this->getPublicKey()
        );
    }

    /**
     * Get a URL for the group endpoint
     *
     * @param string $groupName Name of group
     * @return Http\GroupUrl
     */
    public function getResourceGroupUrl($groupName) {
        $url = sprintf($this->getBaseUrl() . '/groups/%s.json', $groupName);

        return Http\ResourceGroupUrl::factory(
            $url,
            $this->getConfig('privateKey'),
            $this->getPublicKey()
        );
    }

    /**
     * Get a URL for the keys endpoint
     *
     * @return Http\KeysUrl
     */
    public function getKeysUrl() {
        return Http\KeysUrl::factory(
            $this->getBaseUrl() . '/keys.json',
            $this->getConfig('privateKey'),
            $this->getPublicKey()
        );
    }

    /**
     * Get a URL for the key endpoint
     *
     * @param string $publicKey Public key
     * @return Http\KeyUrl
     */
    public function getKeyUrl($publicKey) {
        $url = sprintf($this->getBaseUrl() . '/keys/%s', $publicKey);

        return Http\KeyUrl::factory(
            $url,
            $this->getConfig('privateKey'),
            $this->getPublicKey()
        );
    }

    /**
     * Get a URL for the user endpoint
     *
     * @return Http\UserUrl
     */
    public function getUserUrl() {
        $url = sprintf($this->getBaseUrl() . '/users/%s.json', $this->getUser());

        return Http\UserUrl::factory(
            $url,
            $this->getConfig('privateKey'),
            $this->getPublicKey()
        );
    }

    /**
     * Get a URL for the images resource
     *
     * @return Http\ImagesUrl
     */
    public function getImagesUrl() {
        $url = sprintf($this->getBaseUrl() . '/users/%s/images.json', $this->getUser());

        return Http\ImagesUrl::factory(
            $url,
            $this->getConfig('privateKey'),
            $this->getPublicKey()
        );
    }

    /**
     * Get a URL for the image resource
     *
     * @throws InvalidArgumentException
     * @param string $imageIdentifier An image identifier
     * @return Http\ImageUrl
     */
    public function getImageUrl($imageIdentifier) {
        if (empty($imageIdentifier)) {
            throw new InvalidArgumentException('Missing image identifier');
        }

        $url = sprintf(
            $this->getHostForImageIdentifier($imageIdentifier) . '/users/%s/images/%s',
            $this->getUser(),
            $imageIdentifier
        );

        return Http\ImageUrl::factory(
            $url,
            $this->getConfig('privateKey'),
            $this->getPublicKey()
        );
    }

    /**
     * Get a URL for the metadata resource
     *
     * @param string $imageIdentifier An image identifier
     * @return Http\MetadataUrl
     */
    public function getMetadataUrl($imageIdentifier) {
        $url = sprintf(
            $this->getHostForImageIdentifier($imageIdentifier) . '/users/%s/images/%s/metadata.json',
            $this->getUser(),
            $imageIdentifier
        );

        return Http\MetadataUrl::factory(
            $url,
            $this->getConfig('privateKey'),
            $this->getPublicKey()
        );
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
            // Generate the short URL to fetch the ID
            $response = $this->generateShortUrl($imageUrl);

            $shortUrl = sprintf(
                $this->getBaseUrl() . '/s/%s',
                $response['id']
            );

            if (!$asString) {
                $shortUrl = GuzzleUrl::factory($shortUrl);
            }
        } catch (GuzzleException $e) {
            throw new InvalidArgumentException('Could not generate short URL', 0, $e);
        }

        return $shortUrl;
    }

    /**
     * Fetch the number of images the current user has
     *
     * @return int
     */
    public function getNumImages() {
        $info = $this->getUserInfo();

        return $info['numImages'];
    }

    /**
     * Checks if a given image exists on the server already by specifying a local path
     *
     * @param string $path Path to the local image
     * @throws InvalidArgumentException Throws an exception if the specified file does not exist or
     *                                  is of zero length
     * @return boolean
     */
    public function imageExists($path) {
        $this->validateLocalFile($path);
        $checksum = md5_file($path);
        $query = new ImagesQuery();
        $query->originalChecksums(array($checksum))
              ->limit(1);

        $response = $this->getImages($query);

        return (boolean) $response['search']['hits'];
    }

    /**
     * Checks if a given image exists on the server already by specifying an image identifier
     *
     * @param string $imageIdentifier The image identifier
     * @return boolean
     */
    public function imageIdentifierExists($imageIdentifier) {
        return $this->resourceExists($this->getImageUrl($imageIdentifier));
    }

    /**
     * Get the binary data of an image stored on the server
     *
     * @param string $imageIdentifier The image identifier
     * @return string
     */
    public function getImageData($imageIdentifier) {
        return $this->getImageDataFromUrl($this->getImageUrl($imageIdentifier));
    }

    /**
     * Get the binary data of an image stored on the server
     *
     * @param Url\ImageInterface $url URL instance for the image you want to retrieve
     * @return string
     */
    public function getImageDataFromUrl(Http\ImageUrl $imageUrl) {
        return (string) $this->get((string) $imageUrl)->send()->getBody();
    }

    /**
     * Get a predictable hostname for the given image identifier
     *
     * @param string $imageIdentifier The image identifier
     * @return string
     */
    private function getHostForImageIdentifier($imageIdentifier) {
        $dec = ord(substr($imageIdentifier, -1));

        // If this is an old image identifier (32 character hex string),
        // maintain backwards compatibility
        if (preg_match('#^[a-f0-9]{32}$#', $imageIdentifier)) {
            $dec = hexdec($imageIdentifier[0] . $imageIdentifier[1]);
        }

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
     * Helper method to make sure a public key is valid
     *
     * @param string $name Public key name
     * @throws InvalidArgumentException
     */
    private function validatePublicKeyName($name) {
        return $this->validateResourceGroupName($name, 'Public key');
    }

    /**
     * Helper method to make sure a group name is valid
     *
     * @param string $name The name of the group
     * @param string $entity Entity we're validating
     * @throws InvalidArgumentException
     */
    private function validateResourceGroupName($name, $entity = 'Group name') {
        if (!preg_match('/^[a-z0-9_-]{1,}$/', $name)) {
            throw new InvalidArgumentException(
                $entity . ' can only consist of: a-z, 0-9 and the characters _ and -'
            );
        }
    }

    /**
     * Check if a given resource URL exists (returns a 200 in response to a HEAD-request)
     *
     * @param string $url URL of the resource to check
     * @return boolean
     */
    private function resourceExists($url) {
        try {
            $response = $this->head((string) $url)->send();

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return false;
            }

            throw $e;
        }
    }
}
