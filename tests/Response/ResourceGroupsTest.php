<?php declare(strict_types=1);
namespace ImboClient\Response;

use ImboClient\Query;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\ResourceGroups
 */
class ResourceGroupsTest extends TestCase
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
     * @covers ::getNextQuery
     * @covers ::getPageInfo
     */
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
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => json_encode($resourceGroups),
            ]),
        ]);

        $query = new Query();
        $resourceGroupsResponse = ResourceGroups::fromHttpResponse($response, $query);
        $this->assertSame(3, count($resourceGroupsResponse));
        foreach ($resourceGroupsResponse as $i => $group) {
            $this->assertSame($resourceGroups['groups'][$i]['name'], $group->getName());
        }

        /** @var Query */
        $nextQuery = $resourceGroupsResponse->getNextQuery();
        $this->assertSame(2, $nextQuery->getPage());
        $this->assertSame(1, $resourceGroupsResponse->getPageInfo()->getPage());
    }
}
