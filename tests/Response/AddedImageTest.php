<?php declare(strict_types=1);

namespace ImboClient\Response;

use ImboClient\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(AddedImage::class)]
class AddedImageTest extends TestCase
{
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
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
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
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
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
        $this->assertNull($addedImage['foobar']);
    }

    public function testArrayAccessSetNotSupported(): void
    {
        $response = new AddedImage('image-id', 1024, 768, 'jpg');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not supported');
        $response['imageIdentifier'] = 'new-image-id';
    }

    public function testArrayAccessUnsetNotSupported(): void
    {
        $response = new AddedImage('image-id', 1024, 768, 'jpg');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not supported');
        unset($response['imageIdentifier']);
    }
}
