<?php declare(strict_types=1);

namespace ImboClient;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImagesQuery::class)]
class ImagesQueryTest extends TestCase
{
    public function testCanManipulateQuery(): void
    {
        $query = new ImagesQuery();
        $newQuery = $query
            ->withMetadata(true)
            ->withFrom(123)
            ->withTo(234)
            ->withIds(['id1', 'id2'])
            ->withChecksums(['checksum1', 'checksum2'])
            ->withOriginalChecksums(['checksum3', 'checksum4'])
            ->withSort(['id:desc'])
            ->withAddedSortParameter('size');

        $this->assertEquals([
            'metadata' => false,
            'from' => null,
            'to' => null,
            'ids' => [],
            'checksums' => [],
            'originalChecksums' => [],
            'sort' => [],
            'page' => 1,
            'limit' => 20,
        ], $query->toArray());
        $this->assertEquals([
            'metadata' => true,
            'from' => 123,
            'to' => 234,
            'ids' => ['id1', 'id2'],
            'checksums' => ['checksum1', 'checksum2'],
            'originalChecksums' => ['checksum3', 'checksum4'],
            'sort' => ['id:desc', 'size'],
            'page' => 1,
            'limit' => 20,
        ], $newQuery->toArray());
    }
}
