<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\DeletedShortUrls
 */
class DeletedShortUrlsTest extends TestCase
{
    /**
     * @covers ::fromHttpResponse
     * @covers ::__construct
     * @covers ::getImageIdentifier
     */
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '{"imageIdentifier": "image-id"}',
            ]),
        ]);
        $deletedShortUrls = DeletedShortUrls::fromHttpResponse($response);
        $this->assertSame('image-id', $deletedShortUrls->getImageIdentifier());
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
                'getContents' => '{"imageIdentifier": "image-id"}',
            ]),
        ]);
        $deletedShortUrls = DeletedShortUrls::fromHttpResponse($response);

        $this->assertArrayHasKey('imageIdentifier', $deletedShortUrls);
        $this->assertArrayNotHasKey('foobar', $deletedShortUrls);

        $this->assertSame('image-id', $deletedShortUrls['imageIdentifier']);
    }
}
