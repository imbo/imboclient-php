<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private Client $client;
    private string $imboUrl = 'http://imbo';
    private string $user = 'testuser';
    private string $publicKey = 'christer';
    private string $privateKey = 'test';

    /**
     * @param array<int,Response> $responses
     * @param array<array{response:Response,request:Request}> $history
     * @param-out array<array{response:Response,request:Request}> $history
     * @return HttpClient
     */
    private function getMockGuzzleHttpClient(array $responses, array &$history = []): GuzzleHttpClient
    {
        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($history));

        return new GuzzleHttpClient(['handler' => $handler]);
    }

    private function getClient(array $responses, array &$history = [])
    {
        return new Client(
            $this->imboUrl,
            $this->user,
            $this->publicKey,
            $this->privateKey,
            $this->getMockGuzzleHttpClient($responses, $history),
        );
    }
}
