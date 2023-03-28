<?php declare(strict_types=1);
namespace ImboClient;

use ImboClient\Exception\InvalidResponseBodyException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Utils
 */
class UtilsTest extends TestCase
{
    /**
     * @covers ::convertResponseToArray
     */
    public function testThrowsExceptionOnInvalidJsonInResponseBody(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => 'some string',
            ]),
            'getStatusCode' => 200,
        ]);

        $this->expectException(InvalidResponseBodyException::class);
        $this->expectExceptionMessage('Invalid JSON');
        Utils::convertResponseToArray($response);
    }

    /**
     * @covers ::convertResponseToArray
     */
    public function testThrowsExceptionOnValuesOtherThanArrays(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '123',
            ]),
            'getStatusCode' => 200,
        ]);

        $this->expectException(InvalidResponseBodyException::class);
        $this->expectExceptionMessage('Expected JSON array');
        Utils::convertResponseToArray($response);
    }

    /**
     * @covers ::convertResponseToArray
     */
    public function testCanConvertResponseToArray(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '{"foo":"bar"}',
            ]),
            'getStatusCode' => 200,
        ]);

        $this->assertSame(['foo' => 'bar'], Utils::convertResponseToArray($response));
    }
}
