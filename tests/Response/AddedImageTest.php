<?php declare(strict_types=1);
namespace ImboClient\Response;

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
    }
}
