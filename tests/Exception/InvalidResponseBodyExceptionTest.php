<?php declare(strict_types=1);
namespace ImboClient\Exception;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @coversDefaultClass ImboClient\Exception\InvalidResponseBodyException
 */
class InvalidResponseBodyExceptionTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getResponse
     */
    public function testCanGetResponse(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getStatusCode' => 400,
        ]);
        $exception = new InvalidResponseBodyException('some message', $response);
        $this->assertSame($response, $exception->getResponse());
        $this->assertSame(400, $exception->getCode());
    }
}
