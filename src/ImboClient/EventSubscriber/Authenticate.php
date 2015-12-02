<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Guzzle\Http\Message\Request,
    Guzzle\Common\Event;

/**
 * Authenticate event subscriber
 *
 * This subscriber is used to sign the request with authentication info
 *
 * @package Client\Event subscribers
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Authenticate implements EventSubscriberInterface {
    /**
     * Get events this subscriber subscribes to
     *
     * @return array
     */
    public static function getSubscribedEvents() {
        return array(
            'command.before_send' => array('signRequest', -1000),
        );
    }

    /**
     * Sign the request by adding some special request headers
     *
     * @param Event $event The current event
     */
    public function signRequest(Event $event) {
        $command = $event['command'];

        switch ($command->getName()) {
            case 'AddImage':
            case 'DeleteImage':
            case 'ReplaceMetadata':
            case 'EditMetadata':
            case 'DeleteMetadata':
            case 'GenerateShortUrl':
            case 'EditResourceGroup':
            case 'DeleteResourceGroup':
            case 'EditPublicKey':
            case 'DeletePublicKey':
            case 'AddAccessControlRules':
            case 'DeleteAccessControlRule':
                // Add the auth headers
                $this->addAuthenticationHeaders($command->getRequest());
                break;
        }
    }

    /**
     * Sign the current request for write operations
     *
     * @param Request $request The current request
     */
    private function addAuthenticationHeaders(Request $request) {
        $client = $request->getClient();

        // Get a GMT/UTC timestamp
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        // Build the data to base the hash on
        $data = $request->getMethod() . '|' .
                $request->getUrl() . '|' .
                $client->getConfig('publicKey') . '|' .
                $timestamp;

        // Generate signature
        $signature = hash_hmac('sha256', $data, $client->getConfig('privateKey'));

        // Add relevant request headers (overwriting once that might already exist)
        $request->setHeader('X-Imbo-Authenticate-Signature', $signature);
        $request->setHeader('X-Imbo-Authenticate-Timestamp', $timestamp);
    }
}
