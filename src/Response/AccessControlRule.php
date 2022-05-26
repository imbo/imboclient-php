<?php declare(strict_types=1);
namespace ImboClient\Response;

use ImboClient\Exception\InvalidResponseBodyException;
use ImboClient\Utils;
use Psr\Http\Message\ResponseInterface;

class AccessControlRule extends ApiResponse
{
    private string $id;
    /** @var string|array<string> */
    private $users;
    /** @var ?array<string> */
    private ?array $resources;
    private ?string $group;

    /**
     * @param string|array<string> $users
     * @param ?array<string> $resources
     */
    public function __construct(string $id, $users, array $resources = null, string $group = null)
    {
        $this->id        = $id;
        $this->users     = $users;
        $this->resources = $resources;
        $this->group     = $group;
    }
    /**
     * @throws InvalidResponseBodyException
     */
    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array{id:string,resources?:array<string>,group?:string,users:string|array<string>} */
        $body = Utils::convertResponseToArray($response);
        return (new self($body['id'], $body['users'], $body['resources'] ?? null, $body['group'] ?? null))->withResponse($response);
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|array<string>
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function getResources(): ?array
    {
        return $this->resources;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    protected function getArrayOffsets(): array
    {
        return [
            'id' => fn (): string => $this->getId(),
            'users' => fn () => $this->getUsers(),
            'resources' => fn (): ?array => $this->getResources(),
            'group' => fn (): ?string => $this->getGroup(),
        ];
    }
}
