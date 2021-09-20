<?php declare(strict_types=1);
namespace ImboClient;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass ImboClient\Query
 */
class QueryTest extends TestCase
{
    private Query $query;

    protected function setUp(): void
    {
        $this->query = new Query();
    }

    /**
     * @covers ::withPage
     * @covers ::getPage
     */
    public function testCanSetPage(): void
    {
        $query = $this->query->withPage(123);
        $this->assertNotSame($query, $this->query);
        $this->assertSame(123, $query->getPage());
        $this->assertSame(1, $this->query->getPage());
    }

    /**
     * @covers ::withLimit
     * @covers ::getLimit
     */
    public function testCanSetLimit(): void
    {
        $query = $this->query->withLimit(2);
        $this->assertNotSame($query, $this->query);
        $this->assertSame(2, $query->getLimit());
        $this->assertSame(20, $this->query->getLimit());
    }

    /**
     * @covers ::toArray
     */
    public function testCanConvertToArray(): void
    {
        $this->assertEquals([
            'page'  => 1,
            'limit' => 20,
        ], $this->query->toArray());
    }
}
