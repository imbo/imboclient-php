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

use Guzzle\Common\Collection,
    Guzzle\Common\Event,
    Guzzle\Service\Client,
    Guzzle\Service\Description\ServiceDescription,
    Guzzle\Http\Message\Request;

/**
 * Client that interacts with Imbo servers
 *
 * This client includes methods that can be used to easily interact with Imbo servers.
 *
 * @package Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ImboClient extends Client implements ImboClientInterface {
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

        // Attach event listeners that handles the signing of write operations and the attachment of
        // access tokens to read operations
        $this->getEventDispatcher()->addListener('client.command.create', function($event) {
            $commandName = $event['command']->getName();
            $dispatcher = $event->getDispatcher();

            switch ($commandName) {
                case 'GetServerStatus':
                case 'GetUserInfo':
                case 'TransformImage':
                case 'GetImages':
                case 'GetMetadata':
                    // Generate access token
                    $dispatcher->addListener('request.before_send', function($event) {
                        $this->addAccessToken($event['request']);
                    }, -1000);
                    break;
                case 'AddImage':
                case 'DeleteImage':
                case 'ReplaceMetadata':
                case 'EditMetadata':
                case 'DeleteMetadata':
                    // Sign the request
                    $dispatcher->addListener('request.before_send', function($event) {
                        $this->signRequest($event['request']);
                    } -1000);
                    break;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function factory($config = array()) {
        $default = array(
            'baseUrl' => null,
            'publicKey' => null,
            'privateKey' => null,
        );

        $required = array('baseUrl', 'publicKey', 'privateKey');
        $config = Collection::fromConfig($config, $default, $required);

        // Create the client and attach the service description
        $description = ServiceDescription::factory(__DIR__ . '/service.php');
        $client = new self($config->get('baseUrl'), $config);
        $client->setDescription($description);

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerStatus() {
        return $this->getCommand('GetServerStatus')->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo() {
        return $this->getCommand('GetUserInfo', array(
            'publicKey' => $this->getConfig('publicKey'),
        ))->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function addImage($path) {
        $this->validateLocalFile($path);

        return $this->addImageFromString(file_get_contents($path));

    }

    /**
     * {@inheritdoc}
     */
    public function addImageFromUrl($url) {
        $image = (string) $this->get((string) $url)->send()->getBody();

        return $this->addImageFromString($image);
    }

    /**
     * {@inheritdoc}
     */
    public function addImageFromString($image) {
        if (empty($image)) {
            throw new InvalidArgumentException('Specified image is empty');
        }

        return $this->getCommand('AddImage', array(
            'publicKey' => $this->getConfig('publicKey'),
            'image' => $image,
            'imageIdentifier' => md5($image),
        ))->execute();
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
     * Add an access token to the request
     *
     * @param Request $request The current request
     */
    private function addAccessToken(Request $request) {
        $accessToken = $this->hash($request->getUrl(), $this->getConfig('privateKey'));
        $request->getQuery()->set('accessToken', $accessToken);
    }

    /**
     * Sign the current request for write operations
     *
     * @param Request $request The current request
     */
    private function signRequest(Request $request) {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $data = $request->getMethod() . '|' .
                $request->getUrl() . '|' .
                $this->getConfig('publicKey') . '|' .
                $timestamp;

        // Generate signature
        $signature = $this->hash($data, $this->getConfig('privateKey'));

        // Add relevant request headers
        $request->addHeaders(array(
            'X-Imbo-Authenticate-Signature' => $signature,
            'X-Imbo-Authenticate-Timestamp' => $timestamp,
        ));
    }

    /**
     * Generate a keyed hash
     *
     * @param string $data The data to be hashed
     * @param string $key Shared secret key
     * @return string Returns the keyed hash
     */
    private function hash($data, $privateKey) {
        return hash_hmac('sha256', $data, $privateKey);
    }
}
