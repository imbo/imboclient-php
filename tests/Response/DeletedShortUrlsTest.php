<?php declare(strict_types=1);

namespace ImboClient\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(DeletedShortUrls::class)]
class DeletedShortUrlsTest extends TestCase
{
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"imageIdentifier": "image-id"}',
            ]),
        ]);
        $deletedShortUrls = DeletedShortUrls::fromHttpResponse($response);
        $this->assertSame('image-id', $deletedShortUrls->getImageIdentifier());
    }

    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"imageIdentifier": "image-id"}',
            ]),
        ]);
        $deletedShortUrls = DeletedShortUrls::fromHttpResponse($response);

        $this->assertArrayHasKey('imageIdentifier', $deletedShortUrls);
        $this->assertArrayNotHasKey('foobar', $deletedShortUrls);

        $this->assertSame('image-id', $deletedShortUrls['imageIdentifier']);
    }
}
