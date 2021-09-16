<?php declare(strict_types=1);
namespace ImboClient\Response;

use ImboClient\Utils;
use Psr\Http\Message\ResponseInterface;

class DeletedShortUrls
{
    private string $imageIdentifier;

    public function __construct(string $imageIdentifier)
    {
        $this->imageIdentifier = $imageIdentifier;
    }

    public function getImageIdentifier(): string
    {
        return $this->imageIdentifier;
    }

    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array{imageIdentifier:string} */
        $body = Utils::convertResponseToArray($response);
        return new self($body['imageIdentifier']);
    }
}
