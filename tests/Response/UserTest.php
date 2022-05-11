<?php declare(strict_types=1);
namespace ImboClient\Response;

use DateTime;
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

    /**
     * @covers ::offsetExists
     * @covers ::offsetGet
     * @covers ::getArrayOffsets
     */
    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '{"user":"user","numImages":3,"lastModified":"Mon, 20 Sep 2021 20:33:57 GMT"}',
            ]),
        ]);
        $user = User::fromHttpResponse($response);

        $this->assertArrayHasKey('user', $user);
        $this->assertArrayHasKey('numImages', $user);
        $this->assertArrayHasKey('lastModified', $user);
        $this->assertArrayNotHasKey('foobar', $user);

        /** @var DateTime */
        $date = $user['lastModified'];

        $this->assertSame(1632170037, $date->getTimestamp());
        $this->assertSame('user', $user['user']);
        $this->assertSame(3, $user['numImages']);
    }
}
