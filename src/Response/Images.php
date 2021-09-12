<?php declare(strict_types=1);
namespace ImboClient\Response;

use ArrayObject;
use DateTime;
use ImboClient\Exception\InvalidResponseBodyException;
use ImboClient\ImagesQuery;
use ImboClient\Response\Images\Image;
use Iterator;
use Psr\Http\Message\ResponseInterface;

class Images extends Response implements Iterator
{
    private int $iteratorIndex = 0;
    private PageInfo $page;
    private ?ImagesQuery $nextQuery;
    /** @var array<Image> */
    private array $images;

    /**
     * @param array<Image> $images
     */
    public function __construct(array $images, PageInfo $page, ?ImagesQuery $nextQuery = null)
    {
        $this->images    = $images;
        $this->page      = $page;
        $this->nextQuery = $nextQuery;
    }

    /**
     * @throws InvalidResponseBodyException
     */
    public static function fromHttpResponse(ResponseInterface $response, ImagesQuery $query): self
    {
        /** @var array{search:array<string,int>,images:array<array{imageIdentifier:string,checksum:string,originalChecksum:string,user:string,added:string,updated:string,size:int,width:int,height:int,mime:string,extension:string,metadata:array}>} */
        $body = self::convertResponseToArray($response);

        $images = array_map(function (array $image): Image {
            return new Image(
                $image['imageIdentifier'],
                $image['checksum'],
                $image['originalChecksum'],
                $image['user'],
                new DateTime($image['added']),
                new DateTime($image['updated']),
                $image['size'],
                $image['width'],
                $image['height'],
                $image['mime'],
                $image['extension'],
                new ArrayObject($image['metadata']),
            );
        }, $body['images']);

        $search = $body['search'];
        $pageInfo = new PageInfo($search['hits'], $search['page'], $search['limit'], $search['count']);
        $nextQuery = null;

        if (($query->getPage() * $query->getLimit()) < $search['hits']) {
            $nextQuery = $query->withPage($query->getPage() + 1);
        }

        return new self(
            $images,
            $pageInfo,
            $nextQuery,
        );
    }

    public function getPageInfo(): PageInfo
    {
        return $this->page;
    }

    public function getNextQuery(): ?ImagesQuery
    {
        return $this->nextQuery;
    }

    public function rewind(): void
    {
        $this->iteratorIndex = 0;
    }

    public function current(): Image
    {
        return $this->images[$this->iteratorIndex];
    }

    public function key(): string
    {
        return $this->images[$this->iteratorIndex]->getImageIdentifier();
    }

    public function next(): void
    {
        $this->iteratorIndex++;
    }

    public function valid(): bool
    {
        return isset($this->images[$this->iteratorIndex]);
    }
}
