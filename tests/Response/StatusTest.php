<?php declare(strict_types=1);
namespace ImboClient\Response;

use DateTime;
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
        $this->assertSame($response, $status->getResponse());
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
                'getContents' => '{"date":"Mon, 20 Sep 2021 20:33:57 GMT","database":true,"storage":false}',
            ]),
            'getStatusCode' => 503,
            'getReasonPhrase' => 'storage error',
        ]);
        $status = Status::fromHttpResponse($response);

        $this->assertArrayHasKey('date', $status);
        $this->assertArrayHasKey('database', $status);
        $this->assertArrayHasKey('storage', $status);
        $this->assertArrayNotHasKey('foobar', $status);

        /** @var DateTime */
        $date = $status['date'];

        $this->assertSame(1632170037, $date->getTimestamp());
        $this->assertTrue($status['database']);
        $this->assertFalse($status['storage']);
    }
}
