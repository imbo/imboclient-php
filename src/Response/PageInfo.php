<?php declare(strict_types=1);
namespace ImboClient\Response;

class PageInfo extends ApiResponse
{
    private int $hits;
    private int $page;
    private int $limit;
    private int $count;

    public function __construct(int $hits, int $page, int $limit, int $count)
    {
        $this->hits = $hits;
        $this->page = $page;
        $this->limit = $limit;
        $this->count = $count;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    protected function getArrayOffsets(): array
    {
        return [
            'hits' => fn (): int => $this->getHits(),
            'page' => fn (): int => $this->getPage(),
            'limit' => fn (): int => $this->getLimit(),
            'count' => fn (): int => $this->getCount(),
        ];
    }
}
