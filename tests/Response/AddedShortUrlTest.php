<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\AddedShortUrl
 */
class AddedShortUrlTest extends TestCase
{
    /**
     * @covers ::fromHttpResponse
     * @covers ::__construct
     * @covers ::getId
     */
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '{"id":"id"}',
            ]),
        ]);
        $addedShortUrl = AddedShortUrl::fromHttpResponse($response);
        $this->assertSame('id', $addedShortUrl->getId());
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
                'getContents' => '{"id":"id"}',
            ]),
        ]);
        $addedShortUrl = AddedShortUrl::fromHttpResponse($response);

        $this->assertArrayHasKey('id', $addedShortUrl);
        $this->assertArrayNotHasKey('foobar', $addedShortUrl);

        $this->assertSame('id', $addedShortUrl['id']);
    }
}
