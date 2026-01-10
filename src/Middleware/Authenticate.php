<?php declare(strict_types=1);

namespace ImboClient\Middleware;

use GuzzleHttp\Promise\PromiseInterface;
use ImboClient\Exception\RuntimeException;
use Psr\Http\Message\RequestInterface;

use function array_key_exists;

class Authenticate
{
    private string $publicKey;
    private string $privateKey;

    public function __construct(string $publicKey, string $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    private function addAuthenticationHeaders(RequestInterface $request): RequestInterface
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $data = $request->getMethod().'|'.$request->getUri().'|'.$this->publicKey.'|'.$timestamp;

        return $request
            ->withHeader('X-Imbo-PublicKey', $this->publicKey)
            ->withHeader('X-Imbo-Authenticate-Signature', hash_hmac('sha256', $data, $this->privateKey))
            ->withHeader('X-Imbo-Authenticate-Timestamp', $timestamp);
    }

    public function __invoke(callable $handler): callable
    {
        return function (
            RequestInterface $request,
            array $options,
        ) use ($handler): PromiseInterface {
            if (array_key_exists('require_imbo_signature', $options) && true === $options['require_imbo_signature']) {
                $request = $this->addAuthenticationHeaders($request);
            }

            $result = $handler(
                $request,
                $options,
            );

            if (!$result instanceof PromiseInterface) {
                throw new RuntimeException('Handler function must return a promise');
            }

            return $result;
        };
    }
}
