<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\DeletedImage
 */
class DeletedImageTest extends TestCase
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
        $deletedImage = DeletedImage::fromHttpResponse($response);
        $this->assertSame('image-id', $deletedImage->getImageIdentifier());
        $this->assertSame($response, $deletedImage->getResponse());
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
        $deletedImage = DeletedImage::fromHttpResponse($response);

        $this->assertArrayHasKey('imageIdentifier', $deletedImage);
        $this->assertArrayNotHasKey('foobar', $deletedImage);

        $this->assertSame('image-id', $deletedImage['imageIdentifier']);
        $this->assertSame(null, $deletedImage['foobar']);
    }
}
