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
}
