<?php declare(strict_types=1);

namespace ImboClient\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(PublicKey::class)]
class PublicKeyTest extends TestCase
{
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"publicKey": "my-key"}',
            ]),
        ]);
        $publicKey = PublicKey::fromHttpResponse($response);
        $this->assertSame('my-key', $publicKey->getPublicKey());
    }

    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"publicKey": "my-key"}',
            ]),
        ]);
        $publicKey = PublicKey::fromHttpResponse($response);

        $this->assertArrayHasKey('publicKey', $publicKey);
        $this->assertArrayNotHasKey('foobar', $publicKey);

        $this->assertSame('my-key', $publicKey['publicKey']);
    }
}
