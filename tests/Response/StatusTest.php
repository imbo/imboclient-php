<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\Status
 */
class StatusTest extends TestCase
{
    /**
     * @covers ::fromHttpResponse
     * @covers ::__construct
     * @covers ::getDate
     * @covers ::isHealthy
     * @covers ::isDatabaseHealthy
     * @covers ::isStorageHealthy
     */
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '{"date":"Mon, 20 Sep 2021 20:33:57 GMT","database":true,"storage":false}',
            ]),
        ]);
        $status = Status::fromHttpResponse($response);
        $this->assertSame(1632170037, $status->getDate()->getTimestamp());
        $this->assertTrue($status->isDatabaseHealthy());
        $this->assertFalse($status->isStorageHealthy());
        $this->assertFalse($status->isHealthy());
    }
}
