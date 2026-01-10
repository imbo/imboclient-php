<?php declare(strict_types=1);

namespace ImboClient\Response;

use ImboClient\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(ResourceGroups::class)]
class ResourceGroupsTest extends TestCase
{
    public function testCanCreateFromResponse(): void
    {
        $resourceGroups = [
            'search' => [
                'hits' => 100,
                'page' => 1,
                'limit' => 3,
                'count' => 3,
            ],
            'groups' => [
                [
                    'name' => 'name-1',
                    'resources' => [],
                ],
                [
                    'name' => 'name-2',
                    'resources' => [],
                ],
                [
                    'name' => 'name-3',
                    'resources' => [],
                ],
            ],
        ];
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => json_encode($resourceGroups),
            ]),
        ]);

        $resourceGroupsResponse = ResourceGroups::fromHttpResponse($response, new Query());
        $this->assertCount(3, $resourceGroupsResponse);
        foreach ($resourceGroupsResponse as $i => $group) {
            $this->assertSame($resourceGroups['groups'][$i]['name'], $group->getName());
        }

        /** @var Query */
        $nextQuery = $resourceGroupsResponse->getNextQuery();
        $this->assertSame(2, $nextQuery->getPage());
        $this->assertSame(1, $resourceGroupsResponse->getPageInfo()->getPage());
    }

    public function testArrayAccess(): void
    {
        $resourceGroups = [
            'search' => [
                'hits' => 100,
                'page' => 1,
                'limit' => 3,
                'count' => 3,
            ],
            'groups' => [
                [
                    'name' => 'name-1',
                    'resources' => [],
                ],
                [
                    'name' => 'name-2',
                    'resources' => [],
                ],
                [
                    'name' => 'name-3',
                    'resources' => [],
                ],
            ],
        ];
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => json_encode($resourceGroups),
            ]),
        ]);
        $groups = ResourceGroups::fromHttpResponse($response, new Query());

        $this->assertArrayHasKey('groups', $groups);
        $this->assertArrayNotHasKey('foobar', $groups);

        /** @var array<int, ResourceGroup> */
        $rg = $groups['groups'];

        $this->assertCount(3, $rg);

        foreach ($rg as $i => $group) {
            $this->assertSame($resourceGroups['groups'][$i]['name'], $group->getName());
        }
    }
}
