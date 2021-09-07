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
            'command.before_send' => array('addPublicKey', -500),
        );
    }

    /**
     * Add a public key header to the request
     *
     * @param Event $event The current event
     */
    public function addPublicKey(Event $event) {
        $command = $event['command'];
        $request = $command->getRequest();
        $url = $request->getUrl(true);

        // Don't add public key if query string already contains public key
        if ($url->getQuery()->hasKey('publicKey')) {
            return;
        }

        $client = $request->getClient();
        $publicKey = $client->getPublicKey();
        $user = $client->getUser();

        // No need for the header if the user and public key matches
        if ($user && $user === $publicKey) {
            return;
        }

        $request->setHeader('X-Imbo-PublicKey', $publicKey);
    }
}
