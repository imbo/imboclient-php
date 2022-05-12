<?php declare(strict_types=1);
namespace ImboClient\Response;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass ImboClient\Response\ImageProperties
 */
class ImagePropertiesTest extends TestCase
{
    /**
     * @covers ::fromHttpResponse
     * @covers ::__construct
     * @covers ::getImageIdentifier
     * @covers ::getOriginalSize
     * @covers ::getOriginalWidth
     * @covers ::getOriginalHeight
     * @covers ::getOriginalMimeType
     * @covers ::getOriginalExtension
     */
    public function testCanCreateFromResponse(): void
    {
        $imageProperties = ImageProperties::fromHttpResponse(new Response(200, [
            'x-imbo-imageidentifier' => 'image-id',
            'x-imbo-originalfilesize' => '123',
            'x-imbo-originalwidth' => '456',
            'x-imbo-originalheight' => '789',
            'x-imbo-originalmimetype' => 'image/png',
            'x-imbo-originalextension' => 'png',
        ]));
        $this->assertSame('image-id', $imageProperties->getImageIdentifier());
        $this->assertSame(123, $imageProperties->getOriginalSize());
        $this->assertSame(456, $imageProperties->getOriginalWidth());
        $this->assertSame(789, $imageProperties->getOriginalHeight());
        $this->assertSame('image/png', $imageProperties->getOriginalMimeType());
        $this->assertSame('png', $imageProperties->getOriginalExtension());
    }

    /**
     * @covers ::offsetExists
     * @covers ::offsetGet
     * @covers ::getArrayOffsets
     */
    public function testArrayAccess(): void
    {
        $imageProperties = ImageProperties::fromHttpResponse(new Response(200, [
            'x-imbo-imageidentifier' => 'image-id',
            'x-imbo-originalfilesize' => '123',
            'x-imbo-originalwidth' => '456',
            'x-imbo-originalheight' => '789',
            'x-imbo-originalmimetype' => 'image/png',
            'x-imbo-originalextension' => 'png',
        ]));

        $this->assertArrayHasKey('imageIdentifier', $imageProperties);
        $this->assertArrayHasKey('size', $imageProperties);
        $this->assertArrayHasKey('width', $imageProperties);
        $this->assertArrayHasKey('height', $imageProperties);
        $this->assertArrayHasKey('mimetype', $imageProperties);
        $this->assertArrayHasKey('extension', $imageProperties);
        $this->assertArrayNotHasKey('foobar', $imageProperties);

        $this->assertSame('image-id', $imageProperties['imageIdentifier']);
        $this->assertSame(123, $imageProperties['size']);
        $this->assertSame(456, $imageProperties['width']);
        $this->assertSame(789, $imageProperties['height']);
        $this->assertSame('image/png', $imageProperties['mimetype']);
        $this->assertSame('png', $imageProperties['extension']);
    }
}
