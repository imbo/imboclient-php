<?php declare(strict_types=1);

namespace ImboClient\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(DeletedShortUrl::class)]
class DeletedShortUrlTest extends TestCase
{
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"id": "id"}',
            ]),
        ]);
        $deletedShortUrl = DeletedShortUrl::fromHttpResponse($response);
        $this->assertSame('id', $deletedShortUrl->getId());
    }

    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"id": "id"}',
            ]),
        ]);
        $deletedShortUrl = DeletedShortUrl::fromHttpResponse($response);

        $this->assertArrayHasKey('id', $deletedShortUrl);
        $this->assertArrayNotHasKey('foobar', $deletedShortUrl);

        $this->assertSame('id', $deletedShortUrl['id']);
    }
}
