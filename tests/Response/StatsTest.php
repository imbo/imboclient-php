<?php declare(strict_types=1);

namespace ImboClient\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(Stats::class)]
class StatsTest extends TestCase
{
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"numImages":123,"numUsers":10,"numBytes":123123123,"custom":{"my-stats":200}}',
            ]),
        ]);
        $stats = Stats::fromHttpResponse($response);
        $this->assertSame(123, $stats->getNumImages());
        $this->assertSame(10, $stats->getNumUsers());
        $this->assertSame(123123123, $stats->getNumBytes());
        $this->assertSame(['my-stats' => 200], $stats->getCustomStats());
    }

    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"numImages":123,"numUsers":10,"numBytes":123123123,"custom":{"my-stats":200}}',
            ]),
        ]);
        $stats = Stats::fromHttpResponse($response);

        $this->assertArrayHasKey('numImages', $stats);
        $this->assertArrayHasKey('numUsers', $stats);
        $this->assertArrayHasKey('numBytes', $stats);
        $this->assertArrayHasKey('custom', $stats);
        $this->assertArrayNotHasKey('foobar', $stats);

        $this->assertSame(123, $stats['numImages']);
        $this->assertSame(10, $stats['numUsers']);
        $this->assertSame(123123123, $stats['numBytes']);
        $this->assertSame(['my-stats' => 200], $stats['custom']);
    }
}
