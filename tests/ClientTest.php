<?php declare(strict_types=1);
namespace ImboClient;

use ArrayObject;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends TestCase
{
    private Client $client;
    private string $imboUrl = 'http://imbo';
    private string $user = 'testuser';
    private string $publicKey = 'christer';
    private string $privateKey = 'test';
    private ArrayObject $historyContainer;

    protected function setUp(): void
    {
        $this->historyContainer = new ArrayObject();
    }

    /**
     * @param array<int,ResponseInterface> $responses
     * @return GuzzleHttpClient
     */
    private function getMockGuzzleHttpClient(array $responses): GuzzleHttpClient
    {
        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($this->historyContainer));
        return new GuzzleHttpClient(['handler' => $handler]);
    }

    /**
     * @param array<int,ResponseInterface> $responses
     */
    private function getClient(array $responses): Client
    {
        return new Client(
            $this->imboUrl,
            $this->user,
            $this->publicKey,
            $this->privateKey,
            $this->getMockGuzzleHttpClient($responses),
        );
    }
}
