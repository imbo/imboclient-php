<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\ResourceGroup
 */
class ResourceGroupTest extends TestCase
{
    /**
     * @covers ::fromHttpResponse
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getResources
     */
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '{"name": "name","resources":["resource"]}',
            ]),
        ]);
        $resourceGroup = ResourceGroup::fromHttpResponse($response);
        $this->assertSame('name', $resourceGroup->getName());
        $this->assertSame(['resource'], $resourceGroup->getResources());
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
                'getContents' => '{"name": "name","resources":["resource"]}',
            ]),
        ]);
        $group = ResourceGroup::fromHttpResponse($response);

        $this->assertArrayHasKey('name', $group);
        $this->assertArrayHasKey('resources', $group);
        $this->assertArrayNotHasKey('foobar', $group);

        $this->assertSame('name', $group['name']);
        $this->assertSame(['resource'], $group['resources']);
    }
}
