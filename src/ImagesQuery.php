<?php declare(strict_types=1);
namespace ImboClient;

class ImagesQuery
{
    private int $page = 1;
    private int $limit = 20;
    private bool $returnMetadata = false;
    private ?int $from = null;
    private ?int $to = null;
    private array $ids = [];
    private array $checksums = [];
    private array $originalChecksums = [];
    private array $sort = [];

    public function withPage(int $page): self
    {
        $clone = clone $this;
        $clone->page = $page;
        return $clone;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function withLimit(int $limit): self
    {
        $clone = clone $this;
        $clone->limit = $limit;
        return $clone;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function withReturnMetadata(bool $returnMetadata): self
    {
        $clone = clone $this;
        $clone->returnMetadata = $returnMetadata;
        return $clone;
    }

    public function withFrom(int $from): self
    {
        $clone = clone $this;
        $clone->from = $from;
        return $clone;
    }

    public function withTo(int $to): self
    {
        $clone = clone $this;
        $clone->to = $to;
        return $clone;
    }

    public function withIds(array $ids): self
    {
        $clone = clone $this;
        $clone->ids = $ids;
        return $clone;
    }

    public function withChecksums(array $checksums): self
    {
        $clone = clone $this;
        $clone->checksums = $checksums;
        return $clone;
    }

    public function withOriginalChecksums(array $originalChecksums): self
    {
        $clone = clone $this;
        $clone->originalChecksums = $originalChecksums;
        return $clone;
    }

    public function withSort(array $sort): self
    {
        $clone = clone $this;
        $clone->sort = $sort;
        return $clone;
    }

    public function withAddedSortParameter(string $sort): self
    {
        $clone = clone $this;
        $clone->sort[] = $sort;
        return $clone;
    }

    /**
     * Convert the query instance to a key => value array
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'page'              => $this->page,
            'limit'             => $this->limit,
            'returnMetadata'    => $this->returnMetadata,
            'from'              => $this->from,
            'to'                => $this->to,
            'ids'               => $this->ids,
            'checksums'         => $this->checksums,
            'originalChecksums' => $this->originalChecksums,
            'sort'              => $this->sort,
        ];
    }
}
