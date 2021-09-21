<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\User
 */
class UserTest extends TestCase
{
    /**
     * @covers ::fromHttpResponse
     * @covers ::__construct
     * @covers ::getUser
     * @covers ::getNumImages
     * @covers ::getLastModified
     */
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '{"user":"user","numImages":3,"lastModified":"Mon, 20 Sep 2021 20:33:57 GMT"}',
            ]),
        ]);
        $user = User::fromHttpResponse($response);
        $this->assertSame('user', $user->getUser());
        $this->assertSame(3, $user->getNumImages());
        $this->assertSame(1632170037, $user->getLastModified()->getTimestamp());
    }
}
