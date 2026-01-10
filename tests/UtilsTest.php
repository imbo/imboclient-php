<?php declare(strict_types=1);

namespace ImboClient;

use ImboClient\Exception\InvalidResponseBodyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(Utils::class)]
class UtilsTest extends TestCase
{
    public function testThrowsExceptionOnInvalidJsonInResponseBody(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => 'some string',
            ]),
            'getStatusCode' => 200,
        ]);

        $this->expectException(InvalidResponseBodyException::class);
        $this->expectExceptionMessage('Invalid JSON');
        Utils::convertResponseToArray($response);
    }

    public function testThrowsExceptionOnValuesOtherThanArrays(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '123',
            ]),
            'getStatusCode' => 200,
        ]);

        $this->expectException(InvalidResponseBodyException::class);
        $this->expectExceptionMessage('Expected JSON array');
        Utils::convertResponseToArray($response);
    }

    public function testCanConvertResponseToArray(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"foo":"bar"}',
            ]),
            'getStatusCode' => 200,
        ]);

        $this->assertSame(['foo' => 'bar'], Utils::convertResponseToArray($response));
    }
}
