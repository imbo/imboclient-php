<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ImboClient\Exception\ClientException;
use ImboClient\Exception\InvalidLocalFileException;
use ImboClient\Exception\RuntimeException;
use ImboClient\Url\ImageUrl;
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
    private array $historyContainer;

    protected function setUp(): void
    {
        $this->historyContainer = [];
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
    private function getClient(array $responses = []): Client
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
        return $this->getPreviousTransaction()['request'];
    }

    /**
     * @return array<int,Request>
     */
    private function getPreviousRequests(int $num): array
    {
        return array_map(
            fn (array $transaction): Request => $transaction['request'],
            $this->getPreviousTransactions($num),
        );
    }

    /**
     * @return array{request:Request,response:Response}
     */
    private function getPreviousTransaction(): array
    {
        return $this->getPreviousTransactions(1)[0];
    }

    /**
     * @return array<int,array{request:Request,response:Response}>
     */
    private function getPreviousTransactions(int $num): array
    {
        if ($num > count($this->historyContainer)) {
            $this->fail('Not enough transactions in the Guzzle history');
        }

        /** @var array<int,array{request:Request,response:Response}> */
        return array_slice($this->historyContainer, -$num);
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
        $uri = $this->getPreviousRequest()->getUri();
        $this->assertSame('/users/testuser/images.json', $uri->getPath());
        $this->assertSame($expectedQueryString, $uri->getQuery());
    }

    /**
     * @covers ::addImageFromString
     */
    public function testAddImageFromString(): void
    {
        $blob = 'some image data';
        $client = $this->getClient([new Response(200, [], '{"imageIdentifier":"id","width":100,"height":100,"extension":"jpg"}')]);
        $_ = $client->addImageFromString($blob);
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images', $request->getUri()->getPath());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame($blob, $request->getBody()->getContents());
    }

    /**
     * @covers ::addImageFromPath
     * @covers ::validateLocalFile
     */
    public function testAddImageFromPath(): void
    {
        $path = __DIR__ . '/_files/image.jpg';
        $client = $this->getClient([new Response(200, [], '{"imageIdentifier":"id","width":100,"height":100,"extension":"jpg"}')]);
        $_ = $client->addImageFromPath($path);
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images', $request->getUri()->getPath());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(file_get_contents($path), $request->getBody()->getContents());
    }

    /**
     * @covers ::addImageFromPath
     * @covers ::validateLocalFile
     */
    public function testAddImageFromPathThrowsExceptionWhenFileDoesNotExist(): void
    {
        $this->expectException(InvalidLocalFileException::class);
        $this->expectExceptionMessage('File does not exist');
        $this->getClient()->addImageFromPath('/foo/bar/baz.jpg');
    }

    /**
     * @covers ::addImageFromPath
     * @covers ::validateLocalFile
     */
    public function testAddImageFromPathThrowsExceptionWhenFileIsEmpty(): void
    {
        $this->expectException(InvalidLocalFileException::class);
        $this->expectExceptionMessage('File is of zero length');
        $this->getClient()->addImageFromPath(__DIR__ . '/_files/emptyImage.png');
    }

    /**
     * @covers ::addImageFromUrl
     */
    public function testAddImageFromUrl(): void
    {
        $url = 'http://example.com/image.jpg';
        $client = $this->getClient([
            new Response(200, [], 'external image blob'),
            new Response(200, [], '{"imageIdentifier":"id","width":100,"height":100,"extension":"jpg"}'),
        ]);
        $_ = $client->addImageFromUrl($url);

        [$externalImageRequest, $imboRequest] = $this->getPreviousRequests(2);

        $this->assertSame($url, (string) $externalImageRequest->getUri());
        $this->assertSame('/users/testuser/images', $imboRequest->getUri()->getPath());
        $this->assertSame('POST', $imboRequest->getMethod());
        $this->assertSame('external image blob', $imboRequest->getBody()->getContents());
    }

    /**
     * @covers ::addImageFromUrl
     */
    public function testAddImageFromUrlThrowsExceptionWhenUnableToFetchImage(): void
    {
        $client = $this->getClient([new Response(404)]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to fetch file at URL');
        $client->addImageFromUrl('http://example.com/image.jpg');
    }

    /**
     * @covers ::deleteImage
     */
    public function testDeleteImage(): void
    {
        $client = $this->getClient([new Response(200, [], '{"imageIdentifier":"some-id"}')]);
        $_ = $client->deleteImage('some-id');
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images/some-id', $request->getUri()->getPath());
        $this->assertSame('DELETE', $request->getMethod());
    }

    /**
     * @covers ::getImageProperties
     */
    public function testGetImageProperties(): void
    {
        $client = $this->getClient([new Response(200)]);
        $_ = $client->getImageProperties('some-id');
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images/some-id', $request->getUri()->getPath());
        $this->assertSame('HEAD', $request->getMethod());
    }

    /**
     * @covers ::getMetadata
     */
    public function testGetMetadata(): void
    {
        $client = $this->getClient([new Response(200, [], '{"some":"data"}')]);
        $metadata = $client->getMetadata('some-id');
        $this->assertSame('/users/testuser/images/some-id/metadata.json', $this->getPreviousRequest()->getUri()->getPath());
        $this->assertSame(['some' => 'data'], $metadata);
    }

    /**
     * @covers ::setMetadata
     */
    public function testSetMetadata(): void
    {
        $client = $this->getClient([new Response(200, [], '{"some":"data"}')]);
        $_ = $client->setMetadata('some-id', ['some' => 'data']);
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images/some-id/metadata', $request->getUri()->getPath());
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('{"some":"data"}', $request->getBody()->getContents());
    }

    /**
     * @covers ::updateMetadata
     */
    public function testUpdateMetadata(): void
    {
        $client = $this->getClient([new Response(200, [], '{"some":"data"}')]);
        $_ = $client->updateMetadata('some-id', ['some' => 'data']);
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images/some-id/metadata', $request->getUri()->getPath());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('{"some":"data"}', $request->getBody()->getContents());
    }

    /**
     * @covers ::deleteMetadata
     */
    public function testDeleteMetadata(): void
    {
        $client = $this->getClient([new Response(200, [], '{}')]);
        $_ = $client->deleteMetadata('some-id');
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images/some-id/metadata', $request->getUri()->getPath());
        $this->assertSame('DELETE', $request->getMethod());
    }

    /**
     * @return array<int,array{serverUrls:array<string>|string,imageIdentifier:string,expectedHost:string}>
     */
    public function getHostsForImageUrl(): array
    {
        $serverUrls = [
            'https://imbo1',
            'https://imbo2',
            'https://imbo3',
            'https://imbo4',
            'https://imbo5',
        ];

        return [
            [
                'serverUrls' => 'https://imbo',
                'imageIdentifier' => 'id-1',
                'expectedHost' => 'imbo',
            ],
            [
                'serverUrls' => $serverUrls,
                'imageIdentifier' => 'id-1',
                'expectedHost' => 'imbo5',
            ],
            [
                'serverUrls' => $serverUrls,
                'imageIdentifier' => 'id-2',
                'expectedHost' => 'imbo1',
            ],
            [
                'serverUrls' => $serverUrls,
                'imageIdentifier' => 'id-3',
                'expectedHost' => 'imbo2',
            ],
            [
                'serverUrls' => $serverUrls,
                'imageIdentifier' => 'id-4',
                'expectedHost' => 'imbo3',
            ],
            [
                'serverUrls' => $serverUrls,
                'imageIdentifier' => 'id-5',
                'expectedHost' => 'imbo4',
            ],
            [
                'serverUrls' => $serverUrls,
                'imageIdentifier' => 'id-6',
                'expectedHost' => 'imbo5',
            ],
        ];
    }

    /**
     * @param array<string>|string $serverUrls
     * @dataProvider getHostsForImageUrl
     * @covers ::__construct
     * @covers ::getImageUrl
     * @covers ::getHostForImageIdentifier
     */
    public function testGetImageUrl($serverUrls, string $imageIdentifier, string $expectedHost): void
    {
        $url = (new Client($serverUrls, 'user', 'pub', 'priv'))->getImageUrl($imageIdentifier);
        $this->assertSame('/users/user/images/' . $imageIdentifier, $url->getPath());
        $this->assertSame($expectedHost, $url->getHost());
    }

    /**
     * @covers ::addShortUrl
     */
    public function testAddShortUrl(): void
    {
        $imageUrl = $this->createConfiguredMock(ImageUrl::class, [
            'getImageIdentifier' => 'image-id',
            'getExtension' => 'png',
            'getQuery' => 't[]=thumbnail',
        ]);

        $client = $this->getClient([new Response(200, [], '{"id":"some-id"}')]);
        $_ = $client->addShortUrl($imageUrl);
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images/image-id/shorturls', $request->getUri()->getPath());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('{"user":"testuser","imageIdentifier":"image-id","extension":"png","query":"t[]=thumbnail"}', $request->getBody()->getContents());
    }

    /**
     * @covers ::deleteImageShortUrls
     */
    public function testDeleteImageShortUrls(): void
    {
        $client = $this->getClient([new Response(200, [], '{"imageIdentifier":"image-id"}')]);
        $_ = $client->deleteImageShortUrls('image-id');
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images/image-id/shorturls', $request->getUri()->getPath());
        $this->assertSame('DELETE', $request->getMethod());
    }

    /**
     * @covers ::getShortUrlProperties
     */
    public function testGetShortUrlProperties(): void
    {
        $client = $this->getClient([new Response(200)]);
        $_ = $client->getShortUrlProperties('short-url-id');
        $request = $this->getPreviousRequest();
        $this->assertSame('/s/short-url-id', $request->getUri()->getPath());
        $this->assertSame('HEAD', $request->getMethod());
    }

    /**
     * @covers ::deleteShortUrl
     */
    public function testDeleteShortUrl(): void
    {
        $client = $this->getClient([
            new Response(200, ['x-imbo-imageidentifier' => 'image-id']),
            new Response(200, [], '{"id":"short-url-id"}'),
        ]);
        $_ = $client->deleteShortUrl('short-url-id');
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images/image-id/shorturls/short-url-id', $request->getUri()->getPath());
        $this->assertSame('DELETE', $request->getMethod());
    }
}
