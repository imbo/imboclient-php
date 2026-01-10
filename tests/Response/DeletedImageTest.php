<?php declare(strict_types=1);

namespace ImboClient\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(DeletedImage::class)]
class DeletedImageTest extends TestCase
{
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"imageIdentifier": "image-id"}',
            ]),
        ]);
        $deletedImage = DeletedImage::fromHttpResponse($response);
        $this->assertSame('image-id', $deletedImage->getImageIdentifier());
        $this->assertSame($response, $deletedImage->getResponse());
    }

    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"imageIdentifier": "image-id"}',
            ]),
        ]);
        $deletedImage = DeletedImage::fromHttpResponse($response);

        $this->assertArrayHasKey('imageIdentifier', $deletedImage);
        $this->assertArrayNotHasKey('foobar', $deletedImage);

        $this->assertSame('image-id', $deletedImage['imageIdentifier']);
        $this->assertNull($deletedImage['foobar']);
    }
}
