<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Middleware;

use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

/**
 * Public key event subscriber
 *
 * This subscriber is used to append a public key query parameter to URL's when the
 * public key and current user does not match
 *
 * @package Client\Event subscribers
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class PublicKey {
    private $publicKey;

    public function __construct($publicKey) {
        $this->publicKey = $publicKey;
    }

    /**
     * Command middleware
     *
     * @param CommandInterface $event The current command
     */
    public function __invoke($handler) {
        return function(CommandInterface $command) use ($handler) {
            $stack = isset($command['@http']['handler']) ? $command['@http']['handler'] : null;
            if (!$stack) {
                $stack = new HandlerStack();
                $stack->setHandler(new CurlHandler());
                $command['@http'] = [ 'handler' => $stack ];
            }
            $stack->push(Middleware::mapRequest(function(RequestInterface $request) {
                return $this->addPublicKey($request);
            }));
            return $handler($command);
        };
    }



    /**
     * Add a public key header to the request
     *
     * @param Event $event The current event
     */
    public function addPublicKey(RequestInterface $request) {
        $url = $request->getUri();

        // Don't add public key if query string already contains public key
        if (preg_match('/\bpublicKey\b/', $url->getQuery())) {
            return;
        }

        return $request->withHeader('X-Imbo-PublicKey', $this->publicKey);
    }
}
