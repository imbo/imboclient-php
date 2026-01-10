<?php declare(strict_types=1);

namespace ImboClient;

class Query
{
    private int $page = 1;
    private int $limit = 20;

    /**
     * @return static
     */
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

    /**
     * @return static
     */
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

    /**
     * Convert the query instance to a key => value array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}
