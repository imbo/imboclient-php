<?php declare(strict_types=1);

namespace ImboClient\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(AccessControlRules::class)]
class AccessControlRulesTest extends TestCase
{
    public function testCanCreateFromResponse(): void
    {
        $rules = [
            [
                'id' => 'id-1',
                'users' => ['user-1'],
                'resources' => ['resource-1'],
                'group' => 'group-1',
            ],
            [
                'id' => 'id-2',
                'users' => ['user-2'],
                'resources' => ['resource-2'],
                'group' => 'group-2',
            ],
        ];
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => json_encode($rules),
            ]),
        ]);

        $accessControlRules = AccessControlRules::fromHttpResponse($response);
        $this->assertCount(2, $accessControlRules);
        foreach ($accessControlRules as $i => $rule) {
            $this->assertSame($rules[$i]['id'], $rule->getId());
        }
    }

    public function testArrayAccess(): void
    {
        $rules = [
            [
                'id' => 'id-1',
                'users' => ['user-1'],
                'resources' => ['resource-1'],
                'group' => 'group-1',
            ],
            [
                'id' => 'id-2',
                'users' => ['user-2'],
                'resources' => ['resource-2'],
                'group' => 'group-2',
            ],
        ];
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => json_encode($rules),
            ]),
        ]);

        $accessControlRules = AccessControlRules::fromHttpResponse($response);

        $this->assertArrayHasKey('rules', $accessControlRules);
        $this->assertArrayNotHasKey('foobar', $accessControlRules);

        /** @var array<int, AccessControlRule> */
        $acrs = $accessControlRules['rules'];

        $this->assertCount(2, $acrs);

        foreach ($acrs as $i => $rule) {
            $this->assertSame($rules[$i]['id'], $rule->getId());
        }
    }
}
