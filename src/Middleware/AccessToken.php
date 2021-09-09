<?php declare(strict_types=1);
namespace ImboClient\Middleware;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class AccessToken
{
    private string $privateKey;

    public function __construct(string $privateKey)
    {
        $this->privateKey = $privateKey;
    }

    private function addAccessTokenToUri(UriInterface $uri): UriInterface
    {
        $uri = Uri::withoutQueryValue($uri, 'accessToken');
        return Uri::withQueryValue($uri, 'accessToken', hash_hmac('sha256', (string) $uri, $this->privateKey));
    }

    public function __invoke(callable $handler): callable
    {
        return function (
            RequestInterface $request,
            array $options
        ) use ($handler): PromiseInterface {
            $result = $handler(
                $request->withUri($this->addAccessTokenToUri($request->getUri())),
                $options,
            );

            if (!$result instanceof PromiseInterface) {
                throw new RuntimeException('Handler function must return a promise');
            }

            return $result;
        };
    }
}
