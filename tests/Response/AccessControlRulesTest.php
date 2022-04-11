<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\AccessControlRules
 */
class AccessControlRulesTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::fromHttpResponse
     * @covers ::count
     * @covers ::rewind
     * @covers ::current
     * @covers ::key
     * @covers ::next
     * @covers ::valid
     */
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
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => json_encode($rules),
            ]),
        ]);

        $accessControlRules = AccessControlRules::fromHttpResponse($response);
        $this->assertSame(2, count($accessControlRules));
        foreach ($accessControlRules as $i => $rule) {
            $this->assertSame($rules[$i]['id'], $rule->getId());
        }
    }
}
