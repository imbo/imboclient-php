<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\Stats
 */
class StatsTest extends TestCase
{
    /**
     * @covers ::fromHttpResponse
     * @covers ::__construct
     * @covers ::getNumImages
     * @covers ::getNumUsers
     * @covers ::getNumBytes
     * @covers ::getCustomStats
     */
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '{"numImages":123,"numUsers":10,"numBytes":123123123,"custom":{"my-stats":200}}',
            ]),
        ]);
        $stats = Stats::fromHttpResponse($response);
        $this->assertSame(123, $stats->getNumImages());
        $this->assertSame(10, $stats->getNumUsers());
        $this->assertSame(123123123, $stats->getNumBytes());
        $this->assertSame(['my-stats' => 200], $stats->getCustomStats());
    }

    /**
     * @covers ::offsetExists
     * @covers ::offsetGet
     * @covers ::getArrayOffsets
     */
    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
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
