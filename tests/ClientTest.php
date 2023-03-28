<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ImboClient\Exception\ClientException;
use ImboClient\Exception\InvalidArgumentException;
use ImboClient\Exception\InvalidLocalFileException;
use ImboClient\Exception\RequestException;
use ImboClient\Exception\RuntimeException;
use ImboClient\Url\AccessTokenUrl;
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
    private string $publicKey = 'user';
    private string $privateKey = 'test';
    private array $historyContainer;

    protected function setUp(): void
    {
        $this->historyContainer = [];
    }

    /**
     * @covers ::getServerStatus
     * @covers ::getUrlForPath
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
     * @covers ::getAccessTokenUrlForPath
     */
    public function testGetUserInfo(): void
    {
        $client = $this->getClient([new Response(200, [], '{"user":"testuser","numImages":0,"lastModified":"Mon, 20 Sep 2021 20:33:57 GMT"}')]);
        $_ = $client->getUserInfo();
        $uri = $this->getPreviousRequest()->getUri();
        $this->assertSame('/users/testuser.json', $uri->getPath());
        $this->assertInstanceOf(AccessTokenUrl::class, $uri);
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
     * @dataProvider getUrlsForAddImage
     * @covers ::addImage
     */
    public function testGenericAddImageWithUrl(string $url): void
    {
        $client = $this->getClient([
            new Response(200, [], 'external image blob'),
            new Response(200, [], '{"imageIdentifier":"id","width":100,"height":100,"extension":"jpg"}'),
        ]);
        $_ = $client->addImage($url);

        [$externalImageRequest, $imboRequest] = $this->getPreviousRequests(2);

        $this->assertSame($url, (string) $externalImageRequest->getUri());
        $this->assertSame('/users/testuser/images', $imboRequest->getUri()->getPath());
        $this->assertSame('POST', $imboRequest->getMethod());
        $this->assertSame('external image blob', $imboRequest->getBody()->getContents());
    }

    /**
     * @covers ::addImage
     */
    public function testGenericAddImageWithLocalPath(): void
    {
        $path = __DIR__ . '/_files/image.jpg';
        $client = $this->getClient([new Response(200, [], '{"imageIdentifier":"id","width":100,"height":100,"extension":"jpg"}')]);
        $_ = $client->addImage($path);
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images', $request->getUri()->getPath());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(file_get_contents($path), $request->getBody()->getContents());
    }

    /**
     * @covers ::addImage
     */
    public function testGenericAddImageWithImageInString(): void
    {
        $blob = 'some image data';
        $client = $this->getClient([new Response(200, [], '{"imageIdentifier":"id","width":100,"height":100,"extension":"jpg"}')]);
        $_ = $client->addImage($blob);
        $request = $this->getPreviousRequest();
        $this->assertSame('/users/testuser/images', $request->getUri()->getPath());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame($blob, $request->getBody()->getContents());
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
     * @dataProvider getHostsForImageUrl
     * @covers ::__construct
     * @covers ::getImageUrl
     * @covers ::getHostForImageIdentifier
     *
     * @param array<string>|string $serverUrls
     */
    public function testGetImageUrl(array|string $serverUrls, string $imageIdentifier, string $expectedHost): void
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

    /**
     * @covers ::imageExists
     */
    public function testImageExists(): void
    {
        $body = <<<JSON
        {
            "search": {
                "hits": 1,
                "page": 1,
                "limit": 1,
                "count": 1
            },
            "images": [
                {
                    "imageIdentifier": "some-id",
                    "checksum": "929db9c5fc3099f7576f5655207eba47",
                    "originalChecksum": "929db9c5fc3099f7576f5655207eba47",
                    "user": "testuser",
                    "added": "Mon, 10 Dec 2012 11:57:51 GMT",
                    "updated":"Mon, 10 Dec 2012 11:57:51 GMT",
                    "size": 41423,
                    "width": 665,
                    "height": 463,
                    "mimeType": "image/png",
                    "extension": "png",
                    "metadata":{}
                }
            ]
        }
        JSON;
        $client = $this->getClient([new Response(200, [], $body)]);
        $this->assertTrue($client->imageExists(__DIR__ . '/_files/image.png'));
        $request = $this->getPreviousRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/users/testuser/images.json', $request->getUri()->getPath());
        $this->assertSame('page=1&limit=1&metadata=0&originalChecksums%5B0%5D=929db9c5fc3099f7576f5655207eba47', $request->getUri()->getQuery());
    }

    /**
     * @covers ::imageExists
     * @covers ::validateLocalFile
     */
    public function testImageExistsThrowsExceptionWhenLocalFileDoesNotExist(): void
    {
        $client = $this->getClient();
        $this->expectException(InvalidLocalFileException::class);
        $this->expectExceptionMessage('File does not exist');
        $client->imageExists('/foo/bar/baz.jpg');
    }

    /**
     * @covers ::imageIdentifierExists
     */
    public function testImageIdentifierExists(): void
    {
        $client = $this->getClient([new Response(200)]);
        $this->assertTrue($client->imageIdentifierExists('image-id'));
        $request = $this->getPreviousRequest();
        $this->assertSame('HEAD', $request->getMethod());
        $this->assertSame('/users/testuser/images/image-id', $request->getUri()->getPath());
    }

    /**
     * @covers ::imageIdentifierExists
     */
    public function testImageIdentifierExistsReturnsFalseOn404(): void
    {
        $client = $this->getClient([new Response(404)]);
        $this->assertFalse($client->imageIdentifierExists('image-id'));
    }

    /**
     * @covers ::imageIdentifierExists
     */
    public function testImageIdentifierExistsThrowsExceptionOnErrors(): void
    {
        $client = $this->getClient([new Response(400)]);
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(400);
        $client->imageIdentifierExists('image-id');
    }

    /**
     * @covers ::getImageData
     * @covers ::getImageDataFromUrl
     */
    public function testGetImageData(): void
    {
        $client = $this->getClient([new Response(200, [], 'image data')]);
        $this->assertSame('image data', $client->getImageData('image-id'));
        $request = $this->getPreviousRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/users/testuser/images/image-id', $request->getUri()->getPath());
    }

    /**
     * @covers ::getImageData
     * @covers ::getImageDataFromUrl
     */
    public function testGetImageDataFromUrlThrowsExceptionOnError(): void
    {
        $client = $this->getClient([new Response(400)]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Unable to fetch file at URL');
        $client->getImageData('image-id');
    }

    /**
     * @covers ::addResourceGroup
     */
    public function testAddResourceGroup(): void
    {
        $client = $this->getClient([
            new Response(404),
            new Response(201, [], '{"name":"my-group","resources":["images"]}'),
        ]);
        $_ = $client->addResourceGroup('my-group', ['images']);
        $request = $this->getPreviousRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/groups', $request->getUri()->getPath());
        $this->assertSame('{"name":"my-group","resources":["images"]}', $request->getBody()->getContents());
    }

    /**
     * @covers ::addResourceGroup
     * @covers ::validateResourceGroupName
     */
    public function testAddResourceGroupThrowsExceptionOnInvalidGroupName(): void
    {
        $client = $this->getClient();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Group name can only consist of');
        $client->addResourceGroup('My Group');
    }

    /**
     * @covers ::addResourceGroup
     */
    public function testAddResourceGroupThrowsExceptionWhenGroupAlreadyExists(): void
    {
        $client = $this->getClient([
            new Response(200, [], '{"name":"my-group","resources":[]}'),
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource group already exists');
        $client->addResourceGroup('my-group');
    }

    /**
     * @covers ::updateResourceGroup
     */
    public function testUpdateResourceGroup(): void
    {
        $client = $this->getClient([
            new Response(200, [], '{"name":"my-group","resources":["images"]}'),
        ]);
        $_ = $client->updateResourceGroup('my-group', ['images']);
        $request = $this->getPreviousRequest();
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/groups/my-group', $request->getUri()->getPath());
        $this->assertSame('{"resources":["images"]}', $request->getBody()->getContents());
    }

    /**
     * @covers ::updateResourceGroup
     */
    public function testUpdateResourceGroupThrowsExceptionOnInvalidGroupName(): void
    {
        $client = $this->getClient();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Group name can only consist of');
        $client->updateResourceGroup('My Group', ['images']);
    }

    /**
     * @covers ::deleteResourceGroup
     */
    public function testDeleteResourceGroup(): void
    {
        $client = $this->getClient([
            new Response(200, [], '{"name":"my-group","resources":[]}'),
        ]);
        $_ = $client->deleteResourceGroup('my-group');
        $request = $this->getPreviousRequest();
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/groups/my-group', $request->getUri()->getPath());
    }

    /**
     * @covers ::deleteResourceGroup
     */
    public function testDeleteResourceGroupThrowsExceptionOnInvalidGroupName(): void
    {
        $client = $this->getClient();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Group name can only consist of');
        $client->deleteResourceGroup('My Group');
    }

    /**
     * @covers ::resourceGroupExists
     */
    public function testResourceGroupExists(): void
    {
        $client = $this->getClient([new Response(200)]);
        $this->assertTrue($client->resourceGroupExists('my-group'));
        $request = $this->getPreviousRequest();
        $this->assertSame('HEAD', $request->getMethod());
        $this->assertSame('/groups/my-group', $request->getUri()->getPath());
        $this->assertSame('publicKey=' . $this->publicKey, $request->getUri()->getQuery());
    }

    /**
     * @covers ::resourceGroupExists
     */
    public function testResourceGroupExistsReturnsFalseWhenGroupDoesNotExist(): void
    {
        $client = $this->getClient([new Response(404)]);
        $this->assertFalse($client->resourceGroupExists('my-group'));
        $request = $this->getPreviousRequest();
        $this->assertSame('HEAD', $request->getMethod());
        $this->assertSame('/groups/my-group', $request->getUri()->getPath());
    }

    /**
     * @covers ::resourceGroupExists
     * @covers ::getHttpResponse
     */
    public function testResourceGroupExistsThrowsExceptionOnError(): void
    {
        $client = $this->getClient([new Response(400)]);
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Imbo request failed');
        $this->expectExceptionCode(400);
        $client->resourceGroupExists('my-group');
    }

    /**
     * @covers ::getResourceGroup
     */
    public function testGetResourceGroup(): void
    {
        $client = $this->getClient([new Response(200, [], '{"name":"my-group","resources":[]}')]);
        $_ = $client->getResourceGroup('my-group');
        $request = $this->getPreviousRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/groups/my-group', $request->getUri()->getPath());
        $this->assertSame('publicKey=' . $this->publicKey, $request->getUri()->getQuery());
    }

    /**
     * @covers ::getResourceGroups
     */
    public function testGetResourceGroups(): void
    {
        $body = <<<JSON
        {
            "search": {
                "hits": 1,
                "page": 1,
                "limit": 1,
                "count": 1
            },
            "groups": [
                {
                    "name": "my-group",
                    "resources": []
                }
            ]
        }
        JSON;
        $client = $this->getClient([new Response(200, [], $body)]);
        $query = (new Query())->withPage(2)->withLimit(3);
        $_ = $client->getResourceGroups($query);
        $request = $this->getPreviousRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/groups', $request->getUri()->getPath());
        $this->assertSame('page=2&limit=3&publicKey=' . $this->publicKey, $request->getUri()->getQuery());
    }

    /**
     * @covers ::addPublicKey
     */
    public function testAddPublicKey(): void
    {
        $client = $this->getClient([
            new Response(404),
            new Response(201, [], '{"publicKey":"public"}'),
        ]);
        $_ = $client->addPublicKey('public', 'private');
        $request = $this->getPreviousRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/keys', $request->getUri()->getPath());
        $this->assertSame('{"publicKey":"public","privateKey":"private"}', $request->getBody()->getContents());
    }

    /**
     * @covers ::addPublicKey
     * @covers ::validatePublicKeyName
     */
    public function testAddPublicKeyThrowsExceptionOnInvalidKeyName(): void
    {
        $client = $this->getClient();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Public key can only consist of');
        $client->addPublicKey('My Public Key', 'private');
    }

    /**
     * @covers ::addPublicKey
     */
    public function testAddPublicKeyThrowsExceptionWhenKeyAlreadyExists(): void
    {
        $client = $this->getClient([
            new Response(200, [], '{"publicKey":"public"}'),
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Public key already exists');
        $client->addPublicKey('public', 'private');
    }

    /**
     * @covers ::updatePublicKey
     */
    public function testUpdatePublicKey(): void
    {
        $client = $this->getClient([
            new Response(200),
            new Response(200, [], '{"publicKey":"public"}'),
        ]);
        $_ = $client->updatePublicKey('public', 'private');
        $request = $this->getPreviousRequest();
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/keys/public', $request->getUri()->getPath());
        $this->assertSame('{"privateKey":"private"}', $request->getBody()->getContents());
    }

    /**
     * @covers ::updatePublicKey
     */
    public function testUpdatePublicKeyThrowsExceptionOnInvalidKeyName(): void
    {
        $client = $this->getClient();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Public key can only consist of');
        $client->updatePublicKey('Public Key', 'private');
    }

    /**
     * @covers ::updatePublicKey
     */
    public function testUpdatePublicKeyThrowsExceptionWhenKeyDoesNotExist(): void
    {
        $client = $this->getClient([new Response(404)]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Public key does not exist');
        $client->updatePublicKey('public', 'private');
    }

    /**
     * @covers ::deletePublicKey
     */
    public function testDeletePublicKey(): void
    {
        $client = $this->getClient([
            new Response(200),
            new Response(200, [], '{"publicKey":"public"}'),
        ]);
        $_ = $client->deletePublicKey('public');
        $request = $this->getPreviousRequest();
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/keys/public', $request->getUri()->getPath());
    }

    /**
     * @covers ::deletePublicKey
     */
    public function testDeletePublicKeyThrowsExceptionOnInvalidKeyName(): void
    {
        $client = $this->getClient();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Public key can only consist of');
        $client->deletePublicKey('My Key');
    }

    /**
     * @covers ::deletePublicKey
     */
    public function testDeletePublicKeyThrowsExceptionWhenKeyDoesNotExist(): void
    {
        $client = $this->getClient([new Response(404)]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Public key does not exist');
        $client->deletePublicKey('public');
    }

    /**
     * @covers ::publicKeyExists
     */
    public function testPublicKeyExists(): void
    {
        $client = $this->getClient([new Response(200)]);
        $this->assertTrue($client->publicKeyExists('public'));
        $request = $this->getPreviousRequest();
        $this->assertSame('HEAD', $request->getMethod());
        $this->assertSame('/keys/public', $request->getUri()->getPath());
        $this->assertSame('publicKey=' . $this->publicKey, $request->getUri()->getQuery());
    }

    /**
     * @covers ::publicKeyExists
     */
    public function testPublicKeyExistsReturnsFalseWhenKeyDoesNotExist(): void
    {
        $client = $this->getClient([new Response(404)]);
        $this->assertFalse($client->publicKeyExists('public'));
        $request = $this->getPreviousRequest();
        $this->assertSame('HEAD', $request->getMethod());
        $this->assertSame('/keys/public', $request->getUri()->getPath());
    }

    /**
     * @covers ::publicKeyExists
     */
    public function testPublicKeyExistsThrowsExceptionOnError(): void
    {
        $client = $this->getClient([new Response(400)]);
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(400);
        $client->publicKeyExists('public');
    }

    /**
     * @covers ::getAccessControlRules
     */
    public function testGetAccessControlRules(): void
    {
        $body = <<<JSON
        [
            {
                "id": "id-1",
                "resources": [],
                "group": "group",
                "users": []
            }
        ]
        JSON;
        $client = $this->getClient([new Response(200, [], $body)]);
        $_ = $client->getAccessControlRules('public');
        $request = $this->getPreviousRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/keys/public/access.json', $request->getUri()->getPath());
        $this->assertSame('publicKey=' . $this->publicKey, $request->getUri()->getQuery());
    }

    /**
     * @covers ::getAccessControlRule
     */
    public function testGetAccessControlRule(): void
    {
        $body = <<<JSON
        {
            "id": "id-1",
            "resources": [],
            "group": "group",
            "users": []
        }
        JSON;
        $client = $this->getClient([new Response(200, [], $body)]);
        $_ = $client->getAccessControlRule('public', 'id-1');
        $request = $this->getPreviousRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/keys/public/access/id-1.json', $request->getUri()->getPath());
        $this->assertSame('publicKey=' . $this->publicKey, $request->getUri()->getQuery());
    }

    /**
     * @covers ::addAccessControlRules
     */
    public function testAddAccessControlRules(): void
    {
        $rules = <<<JSON
        [
            {
                "id": "id-1",
                "resources": [],
                "group": "group",
                "users": []
            }
        ]
        JSON;

        $client = $this->getClient([new Response(200, [], $rules)]);
        $_ = $client->addAccessControlRules('public', [
            [
                'resources' => [],
                'group' => 'group',
                'users' => [],
            ],
        ]);
        $request = $this->getPreviousRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/keys/public/access.json', $request->getUri()->getPath());
        $this->assertSame('[{"resources":[],"group":"group","users":[]}]', $request->getBody()->getContents());
    }

    /**
     * @covers ::deleteAccessControlRule
     */
    public function testDeleteAccessControlRule(): void
    {
        $rule = <<<JSON
        {
            "id": "id-1",
            "resources": [],
            "group": "group",
            "users": []
        }
        JSON;
        $client = $this->getClient([new Response(200, [], $rule)]);
        $_ = $client->deleteAccessControlRule('public', 'id-1');
        $request = $this->getPreviousRequest();
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/keys/public/access/id-1.json', $request->getUri()->getPath());
    }

    /**
     * @return array<string,array{query:?ImagesQuery,expectedQueryString:string}>
     */
    public static function getImagesQuery(): array
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
     * @return array<int,array{serverUrls:array<string>|string,imageIdentifier:string,expectedHost:string}>
     */
    public static function getHostsForImageUrl(): array
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
     * @return array<array{url:string}>
     */
    public static function getUrlsForAddImage(): array
    {
        return [
            ['url' => 'http://example.com/image.jpg'],
            ['url' => 'https://example.com/image.jpg'],
        ];
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
}
