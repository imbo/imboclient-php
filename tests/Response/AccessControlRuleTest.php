<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\AccessControlRule
 */
class AccessControlRuleTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::fromHttpResponse
     * @covers ::getId
     * @covers ::getUsers
     * @covers ::getResources
     * @covers ::getGroup
     */
    public function testCanCreateFromResponse(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => '{"id": "id","users":["user-1","user-2"],"resources":["resource-1"],"group":"group"}',
            ]),
        ]);
        $accessControlRule = AccessControlRule::fromHttpResponse($response);
        $this->assertSame('id', $accessControlRule->getId());
        $this->assertSame(['user-1', 'user-2'], $accessControlRule->getUsers());
        $this->assertSame(['resource-1'], $accessControlRule->getResources());
        $this->assertSame('group', $accessControlRule->getGroup());
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
                'getContents' => '{"id": "id","users":["user-1","user-2"],"resources":["resource-1"],"group":"group"}',
            ]),
        ]);
        $accessControlRule = AccessControlRule::fromHttpResponse($response);

        $this->assertArrayHasKey('id', $accessControlRule);
        $this->assertArrayHasKey('users', $accessControlRule);
        $this->assertArrayHasKey('resources', $accessControlRule);
        $this->assertArrayHasKey('group', $accessControlRule);
        $this->assertArrayNotHasKey('foobar', $accessControlRule);

        $this->assertSame('id', $accessControlRule['id']);
        $this->assertSame(['user-1', 'user-2'], $accessControlRule['users']);
        $this->assertSame(['resource-1'], $accessControlRule['resources']);
        $this->assertSame('group', $accessControlRule['group']);
    }
}
