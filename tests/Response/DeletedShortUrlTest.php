<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\DeletedShortUrl
 */
class DeletedShortUrlTest extends TestCase
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
                'getContents' => '{"id": "id"}',
            ]),
        ]);
        $deletedShortUrl = DeletedShortUrl::fromHttpResponse($response);
        $this->assertSame('id', $deletedShortUrl->getId());
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
                'getContents' => '{"id": "id"}',
            ]),
        ]);
        $deletedShortUrl = DeletedShortUrl::fromHttpResponse($response);

        $this->assertArrayHasKey('id', $deletedShortUrl);
        $this->assertArrayNotHasKey('foobar', $deletedShortUrl);

        $this->assertSame('id', $deletedShortUrl['id']);
    }
}
