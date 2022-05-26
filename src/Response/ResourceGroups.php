<?php declare(strict_types=1);
namespace ImboClient\Response;

use Countable;
use ImboClient\Exception\InvalidResponseBodyException;
use ImboClient\Query;
use ImboClient\Utils;
use Iterator;
use Psr\Http\Message\ResponseInterface;

class ResourceGroups extends ApiResponse implements Iterator, Countable
{
    private int $iteratorIndex = 0;
    private PageInfo $page;
    private ?Query $nextQuery;
    /** @var array<ResourceGroup> */
    private array $resourceGroups;

    /**
     * @param array<ResourceGroup> $resourceGroups
     */
    public function __construct(array $resourceGroups, PageInfo $page, ?Query $nextQuery = null)
    {
        $this->resourceGroups = $resourceGroups;
        $this->page           = $page;
        $this->nextQuery      = $nextQuery;
    }

    /**
     * @throws InvalidResponseBodyException
     */
    public static function fromHttpResponse(ResponseInterface $response, Query $query): self
    {
        /** @var array{search:array<string,int>,groups:array<array{name:string,resources:array<string>}>} */
        $body = Utils::convertResponseToArray($response);

        $resourceGroups = array_map(
            fn (array $group): ResourceGroup => (new ResourceGroup($group['name'], $group['resources']))->withResponse($response),
            $body['groups'],
        );

        $search = $body['search'];
        $pageInfo = new PageInfo($search['hits'], $search['page'], $search['limit'], $search['count']);
        $nextQuery = null;

        if (($query->getPage() * $query->getLimit()) < $search['hits']) {
            $nextQuery = $query->withPage($query->getPage() + 1);
        }

        return (new self($resourceGroups, $pageInfo, $nextQuery))->withResponse($response);
    }

    public function getPageInfo(): PageInfo
    {
        return $this->page;
    }

    public function getNextQuery(): ?Query
    {
        return $this->nextQuery;
    }

    public function rewind(): void
    {
        $this->iteratorIndex = 0;
    }

    public function current(): ResourceGroup
    {
        return $this->resourceGroups[$this->iteratorIndex];
    }

    public function key(): int
    {
        return $this->iteratorIndex;
    }

    public function next(): void
    {
        $this->iteratorIndex++;
    }

    public function valid(): bool
    {
        return isset($this->resourceGroups[$this->iteratorIndex]);
    }

    public function count(): int
    {
        return count($this->resourceGroups);
    }

    protected function getArrayOffsets(): array
    {
        return [
            'groups' => fn (): array => $this->resourceGroups,
        ];
    }
}
