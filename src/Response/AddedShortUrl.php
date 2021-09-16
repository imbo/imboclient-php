<?php declare(strict_types=1);
namespace ImboClient\Response;

use ImboClient\Utils;
use Psr\Http\Message\ResponseInterface;

class AddedShortUrl
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array{id:string} */
        $body = Utils::convertResponseToArray($response);
        return new self($body['id']);
    }
}
