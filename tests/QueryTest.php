<?php declare(strict_types=1);

namespace ImboClient;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Query::class)]
class QueryTest extends TestCase
{
    private Query $query;

    protected function setUp(): void
    {
        $this->query = new Query();
    }

    public function testCanSetPage(): void
    {
        $query = $this->query->withPage(123);
        $this->assertNotSame($query, $this->query);
        $this->assertSame(123, $query->getPage());
        $this->assertSame(1, $this->query->getPage());
    }

    public function testCanSetLimit(): void
    {
        $query = $this->query->withLimit(2);
        $this->assertNotSame($query, $this->query);
        $this->assertSame(2, $query->getLimit());
        $this->assertSame(20, $this->query->getLimit());
    }

    public function testCanConvertToArray(): void
    {
        $this->assertEquals([
            'page' => 1,
            'limit' => 20,
        ], $this->query->toArray());
    }
}
