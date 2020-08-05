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
class Authenticate {
    private $publicKey;
    private $privateKey;

    public function __construct($publicKey, $privateKey) {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * Command middleware
     *
     * @param CommandInterface $event The current command
     */
    public function __invoke($handler) {
        return function(CommandInterface $command) use ($handler) {
            var_dump($command->getName());
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
                    $stack = isset($command['@http']['handler']) ? $command['@http']['handler'] : null;
                    if (!$stack) {
                        $stack = new HandlerStack();
                        $stack->setHandler(new CurlHandler());
                        $command['@http'] = [ 'handler' => $stack ];
                    }
                    $stack->push(Middleware::mapRequest(function(RequestInterface $request) {
                        var_dump($this->addAuthenticationHeaders($request));
                        return $this->addAuthenticationHeaders($request);
                    }));
                    break;
            }
            return $handler($command);
        };
    }


    /**
     * Sign the current request for write operations
     *
     * @param RequestInterface $request The current request
     */
    private function addAuthenticationHeaders(RequestInterface $request) {
        // Get a GMT/UTC timestamp
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        // Build the data to base the hash on
        $data = $request->getMethod() . '|' .
                (string) $request->getUri() . '|' .
                $this->publicKey . '|' .
                $timestamp;

        var_dump($data);

        // Generate signature
        $signature = hash_hmac('sha256', $data, $this->privateKey);

        // Add relevant request headers (overwriting once that might already exist)
        return $request
            ->withHeader('X-Imbo-Authenticate-Signature', $signature)
            ->withHeader('X-Imbo-Authenticate-Timestamp', $timestamp);
    }
}
