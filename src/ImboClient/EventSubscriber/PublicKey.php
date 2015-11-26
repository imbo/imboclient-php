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
 * Public key event subscriber
 *
 * This subscriber is used to append a public key query parameter to URL's when the
 * public key and current user does not match
 *
 * @package Client\Event subscribers
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class PublicKey implements EventSubscriberInterface {
    /**
     * Get events this subscriber subscribes to
     *
     * @return array
     */
    public static function getSubscribedEvents() {
        return array(
            'command.before_send' => array('appendPublicKey', -500),
        );
    }

    /**
     * Append a public key query parameter to the request URL
     *
     * @param Event $event The current event
     */
    public function appendPublicKey(Event $event) {
        $command = $event['command'];
        $request = $command->getRequest();
        $client = $request->getClient();
        $publicKey = $client->getPublicKey();
        $user = $client->getUser();

        // No need for the query parameter if the user and public key matches
        if ($user === $publicKey) {
            return;
        }

        switch ($command->getName()) {
            case 'AddImage':
            case 'DeleteImage':
            case 'DeleteMetadata':
            case 'EditMetadata':
            case 'GenerateShortUrl':
            case 'GetImageProperties':
            case 'GetImages':
            case 'GetMetadata':
            case 'GetUserInfo':
            case 'ReplaceMetadata':
                // Add as query parameter
                $request->getQuery()->set('publicKey', $publicKey);
                break;
        }
    }
}
