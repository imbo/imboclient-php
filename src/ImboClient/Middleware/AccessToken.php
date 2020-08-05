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
 * Access token middleware
 *
 * This subscriber is used to append an access token query parameter to URL's that require this
 *
 * @package Client\Event subscribers
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class AccessToken {
    private $privateKey;

    public function __construct($privateKey) {
        $this->privateKey = $privateKey;
    }
    /**
     * Append an access token query parameter to the request URL
     *
     * @param CommandInterface $event The current command
     */
    public function __invoke($handler) {
        return function(CommandInterface $command) use ($handler) {
            switch ($command->getName()) {
                case 'GetResourceGroup':
                case 'GetResourceGroups':
                case 'GetAccessControlRule':
                case 'GetAccessControlRules':
                case 'GetUserInfo':
                case 'GetImages':
                case 'GetImageProperties':
                case 'GetMetadata':
                    $stack = isset($command['@http']['handler']) ? $command['@http']['handler'] : null;
                    if (!$stack) {
                        $stack = new HandlerStack();
                        $stack->setHandler(new CurlHandler());
                        $command['@http'] = [ 'handler' => $stack ];
                    }
                    $stack->push(Middleware::mapRequest(function(RequestInterface $request) {
                        $accessToken = $this->getAccessToken(urldecode((string) $request->getUri()), $this->privateKey);
                        $uri = Uri::withQueryValue($request->getUri(), 'accessToken', $accessToken);
                        return $request->withUri($uri, true);
                    }));
                    break;
            }
            return $handler($command);
        };
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
