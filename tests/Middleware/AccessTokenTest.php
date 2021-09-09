<?php declare(strict_types=1);
namespace ImboClient\Middleware;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @coversDefaultClass ImboClient\Middleware\AccessToken
 */
class AccessTokenTest extends TestCase
{
    /**
     * @return array<string,array{privateKey:string,requestUri:string,expectedUri:string}>
     */
    public function getUris(): array
    {
        return [
            'no query params' => [
                'privateKey' => 'key',
                'requestUri' => 'http://localhost',
                'expectedUri' => 'http://localhost?accessToken=bf665e1992efad22fa376ca62c7d2b4087132b660d508fbf75edd4fcd8bd3e1f',
            ],
            'existing access token query param' => [
                'privateKey' => 'key',
                'requestUri' => 'http://localhost?accessToken=asdsadasd',
                'expectedUri' => 'http://localhost?accessToken=bf665e1992efad22fa376ca62c7d2b4087132b660d508fbf75edd4fcd8bd3e1f',
            ],
            'multiple query params' => [
                'privateKey' => 'key',
                'requestUri' => 'http://localhost?foo=bar&bar=foo',
                'expectedUri' => 'http://localhost?foo=bar&bar=foo&accessToken=a246dbbe65aac35529a2c1d7b4bd8f4c0ee477f90d7a06cdf3bdd127fedb47ad',
            ],
        ];
    }

    /**
     * @dataProvider getUris
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::addAccessTokenToUri
     */
    public function testSomething(string $privateKey, string $requestUri, string $expectedUri): void
    {
        $handler = function (RequestInterface $request) use ($expectedUri): PromiseInterface {
            $this->assertSame($expectedUri, (string) $request->getUri());
            return $this->createMock(PromiseInterface::class);
        };

        $middleware = new AccessToken($privateKey);
        $middleware($handler)(new Request('GET', $requestUri), []);
    }
}
