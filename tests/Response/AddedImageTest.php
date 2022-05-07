<?php declare(strict_types=1);
namespace ImboClient\Response;

use ImboClient\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\AddedImage
 */
class AddedImageTest extends TestCase
{
    /**
     * @covers ::fromHttpResponse
     * @covers ::__construct
     * @covers ::getImageIdentifier
     * @covers ::getWidth
     * @covers ::getHeight
     * @covers ::getExtension
     * @covers ImboClient\Response\ApiResponse::withResponse
     * @covers ImboClient\Response\ApiResponse::getResponse
     */
    public function testCanCreateFromResponse(): void
    {
        $contents = <<<JSON
        {
            "imageIdentifier": "image-id",
            "width": 1024,
            "height": 768,
            "extension": "jpg"
        }
        JSON;
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => $contents,
            ]),
        ]);
        $addedImage = AddedImage::fromHttpResponse($response);
        $this->assertSame('image-id', $addedImage->getImageIdentifier());
        $this->assertSame(1024, $addedImage->getWidth());
        $this->assertSame(768, $addedImage->getHeight());
        $this->assertSame('jpg', $addedImage->getExtension());
        $this->assertSame($response, $addedImage->getResponse());
    }

    /**
     * @covers ::offsetExists
     * @covers ::offsetGet
     * @covers ::getArrayOffsets
     */
    public function testArrayAccess(): void
    {
        $contents = <<<JSON
        {
            "imageIdentifier": "image-id",
            "width": 1024,
            "height": 768,
            "extension": "jpg"
        }
        JSON;
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => $contents,
            ]),
        ]);
        $addedImage = AddedImage::fromHttpResponse($response);

        $this->assertArrayHasKey('imageIdentifier', $addedImage);
        $this->assertArrayHasKey('width', $addedImage);
        $this->assertArrayHasKey('height', $addedImage);
        $this->assertArrayHasKey('extension', $addedImage);
        $this->assertArrayNotHasKey('foobar', $addedImage);

        $this->assertSame('image-id', $addedImage['imageIdentifier']);
        $this->assertSame(1024, $addedImage['width']);
        $this->assertSame(768, $addedImage['height']);
        $this->assertSame('jpg', $addedImage['extension']);
        $this->assertSame(null, $addedImage['foobar']);
    }

    /**
     * @covers ::offsetSet
     */
    public function testArrayAccessSetNotSupported(): void
    {
        $response = new AddedImage('image-id', 1024, 768, 'jpg');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not supported');
        $response['imageIdentifier'] = 'new-image-id';
    }

    /**
     * @covers ::offsetUnset
     */
    public function testArrayAccessUnsetNotSupported(): void
    {
        $response = new AddedImage('image-id', 1024, 768, 'jpg');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not supported');
        unset($response['imageIdentifier']);
    }
}
