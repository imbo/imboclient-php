<?php declare(strict_types=1);

namespace ImboClient\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(AddedShortUrl::class)]
class AddedShortUrlTest extends TestCase
{
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"id":"id"}',
            ]),
        ]);
        $addedShortUrl = AddedShortUrl::fromHttpResponse($response);
        $this->assertSame('id', $addedShortUrl->getId());
    }

    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"id":"id"}',
            ]),
        ]);
        $addedShortUrl = AddedShortUrl::fromHttpResponse($response);

        $this->assertArrayHasKey('id', $addedShortUrl);
        $this->assertArrayNotHasKey('foobar', $addedShortUrl);

        $this->assertSame('id', $addedShortUrl['id']);
    }
}
