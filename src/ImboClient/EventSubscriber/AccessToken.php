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
    Guzzle\Common\Event;

/**
 * Access token event subscriber
 *
 * This subscriber is used to append an access token query parameter to URL's that require this
 *
 * @package Client\Event subscribers
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class AccessToken implements EventSubscriberInterface {
    /**
     * Get events this subscriber subscribes to
     *
     * @return array
     */
    public static function getSubscribedEvents() {
        return array(
            'command.before_send' => array('appendAccessToken', -1000),
        );
    }

    /**
     * Append an access token query parameter to the request URL
     *
     * @param Event $event The current event
     */
    public function appendAccessToken(Event $event) {
        $command = $event['command'];

        switch ($command->getName()) {
            case 'GetResourceGroup':
            case 'GetResourceGroups':
            case 'GetAccessControlRule':
            case 'GetAccessControlRules':
            case 'GetUserInfo':
            case 'GetImages':
            case 'GetImageProperties':
            case 'GetMetadata':
                $request = $command->getRequest();

                // Generate an access token
                $accessToken = $this->getAccessToken(urldecode($request->getUrl()), $request->getClient()->getConfig('privateKey'));

                // Add as query parameter
                $request->getQuery()->set('accessToken', $accessToken);
                break;
        }
    }

    /**
     * Get an access token for a given URL
     *
     * @param string $url The URL, un-encoded
     * @param string $privateKey The private key used to generate the hash
     * @return string
     */
    public function getAccessToken($url, $privateKey) {
        return hash_hmac('sha256', $url, $privateKey);
    }
}
