<?php declare(strict_types=1);
namespace ImboClient\Response;

use ImboClient\Exception\InvalidResponseBodyException;
use ImboClient\Utils;
use Psr\Http\Message\ResponseInterface;

class ResourceGroup extends ApiResponse
{
    private string $name;
    /** @var array<string> */
    private array $resources;

    /**
     * @param array<string> $resources
     */
    public function __construct(string $name, array $resources)
    {
        $this->name = $name;
        $this->resources = $resources;
    }

    /**
     * @throws InvalidResponseBodyException
     */
    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array{name:string,resources:array<string>} */
        $body = Utils::convertResponseToArray($response);
        return (new self($body['name'], $body['resources']))->withResponse($response);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getResources(): array
    {
        return $this->resources;
    }

    protected function getArrayOffsets(): array
    {
        return [
            'name' => fn (): string => $this->getName(),
            'resources' => fn (): array => $this->getResources(),
        ];
    }
}
