<?php declare(strict_types=1);
namespace ImboClient;

use ArrayObject;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ImboClient\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @coversDefaultClass ImboClient\Client
 */
class ClientTest extends TestCase
{
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

    private function getPreviousRequest(): Request
    {
        if (!count($this->historyContainer)) {
            $this->fail('Expected a request to be present');
        }

        /** @var array{request:Request,response:Response} */
        $transaction = $this->historyContainer[0];
        return $transaction['request'];
    }

    /**
     * @covers ::getServerStatus
     */
    public function testGetServerStatus(): void
    {
        $client = $this->getClient([new Response(200, [], '{"date":"Mon, 20 Sep 2021 20:33:57 GMT","database":true,"storage":true}')]);
        $_ = $client->getServerStatus();
        $this->assertSame('/status.json', $this->getPreviousRequest()->getUri()->getPath());
    }

    /**
     * @covers ::getServerStatus
     */
    public function testGetServerStatusWithServerError(): void
    {
        $client = $this->getClient([new Response(500, [], '{"date":"Mon, 20 Sep 2021 20:33:57 GMT","database":false,"storage":true}')]);
        $_ = $client->getServerStatus();
        $this->assertSame('/status.json', $this->getPreviousRequest()->getUri()->getPath());
    }

    /**
     * @covers ::getServerStatus
     */
    public function testGetServerStatusWithClientError(): void
    {
        $client = $this->getClient([new Response(400, [], '{}')]);
        $this->expectException(ClientException::class);
        $_ = $client->getServerStatus();
    }

    /**
     * @covers ::getServerStats
     */
    public function testGetServerStats(): void
    {
        $client = $this->getClient([new Response(200, [], '{"numImages":0,"numUsers":0,"numBytes":0,"custom":{}}')]);
        $_ = $client->getServerStats();
        $this->assertSame('/stats.json', $this->getPreviousRequest()->getUri()->getPath());
    }

    /**
     * @covers ::getUserInfo
     */
    public function testGetUserInfo(): void
    {
        $client = $this->getClient([new Response(200, [], '{"user":"testuser","numImages":0,"lastModified":"Mon, 20 Sep 2021 20:33:57 GMT"}')]);
        $_ = $client->getUserInfo();
        $this->assertSame('/users/testuser.json', $this->getPreviousRequest()->getUri()->getPath());
    }

    /**
     * @return array<string,array{query:?ImagesQuery,expectedQueryString:string}>
     */
    public function getImagesQuery(): array
    {
        return [
            'no query' => [
                'query' => null,
                'expectedQueryString' => 'page=1&limit=20&metadata=0',
            ],

            'custom query' => [
                'query' => (new ImagesQuery())->withLimit(10)->withIds(['id1', 'id2']),
                'expectedQueryString' => 'page=1&limit=10&metadata=0&ids%5B0%5D=id1&ids%5B1%5D=id2',
            ],
        ];
    }

    /**
     * @dataProvider getImagesQuery
     * @covers ::getImages
     */
    public function testGetImages(?ImagesQuery $query, string $expectedQueryString): void
    {
        $client = $this->getClient([new Response(200, [], '{"search":{"hits":0,"page":1,"limit":10,"count":0},"images":[]}')]);
        $_ = $client->getImages($query);
        $this->assertSame('/users/testuser/images.json', $this->getPreviousRequest()->getUri()->getPath());
        $this->assertSame($expectedQueryString, $this->getPreviousRequest()->getUri()->getQuery());
    }
}
