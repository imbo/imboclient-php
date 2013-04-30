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
    Guzzle\Service\Client,
    Guzzle\Service\Description\ServiceDescription;

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
     * Factory method for creating a new ImboClient instance
     *
     * Configuration parameters:
     *
     * - (string) baseUrl: Base URL to the imbo server
     * - (string) publicKey: The public key to use
     * - (string) privateKey: The private key to use
     *
     * @param array|Collection $config Configuration for the client
     * @return ImboClient
     */
    public static function factory($config = array()) {
        $default = array(
            'baseUrl' => array(),
            'publicKey' => null,
            'privateKey' => null,
        );

        $required = array('baseUrl', 'publicKey', 'privateKey');
        $config = Collection::fromConfig($config, $default, $required);

        $description = ServiceDescription::factory(__DIR__ . '/service.php');

        // Create the client and attach the service description
        $client = new self($config->get('baseUrl'), $config);
        $client->setDescription($description);

        return $client;
    }
}
