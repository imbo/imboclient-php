<?php declare(strict_types=1);

namespace ImboClient\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(ResourceGroup::class)]
class ResourceGroupTest extends TestCase
{
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => '{"name": "name","resources":["resource"]}',
            ]),
        ]);
        $resourceGroup = ResourceGroup::fromHttpResponse($response);
        $this->assertSame('name', $resourceGroup->getName());
        $this->assertSame(['resource'], $resourceGroup->getResources());
    }

    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
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
