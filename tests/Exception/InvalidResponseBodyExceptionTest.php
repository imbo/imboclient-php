<?php declare(strict_types=1);

namespace ImboClient\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(InvalidResponseBodyException::class)]
class InvalidResponseBodyExceptionTest extends TestCase
{
    public function testCanGetResponse(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getStatusCode' => 400,
        ]);
        $exception = new InvalidResponseBodyException('some message', $response);
        $this->assertSame($response, $exception->getResponse());
        $this->assertSame(400, $exception->getCode());
    }
}
