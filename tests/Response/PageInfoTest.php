<?php declare(strict_types=1);
namespace ImboClient\Response;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass ImboClient\Response\PageInfo
 */
class PageInfoTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getHits
     * @covers ::getPage
     * @covers ::getLimit
     * @covers ::getCount
     */
    public function testCanSetAndGetValues(): void
    {
        $pageInfo = new PageInfo(1, 2, 3, 4);
        $this->assertSame(1, $pageInfo->getHits());
        $this->assertSame(2, $pageInfo->getPage());
        $this->assertSame(3, $pageInfo->getLimit());
        $this->assertSame(4, $pageInfo->getCount());
    }

    /**
     * @covers ::offsetExists
     * @covers ::offsetGet
     * @covers ::getArrayOffsets
     */
    public function testArrayAccess(): void
    {
        $pageInfo = new PageInfo(1, 2, 3, 4);

        $this->assertArrayHasKey('hits', $pageInfo);
        $this->assertArrayHasKey('page', $pageInfo);
        $this->assertArrayHasKey('limit', $pageInfo);
        $this->assertArrayHasKey('count', $pageInfo);
        $this->assertArrayNotHasKey('foobar', $pageInfo);

        $this->assertSame(1, $pageInfo['hits']);
        $this->assertSame(2, $pageInfo['page']);
        $this->assertSame(3, $pageInfo['limit']);
        $this->assertSame(4, $pageInfo['count']);
    }
}
