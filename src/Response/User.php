<?php declare(strict_types=1);
namespace ImboClient\Response;

use DateTime;
use ImboClient\Utils;
use Psr\Http\Message\ResponseInterface;

class User extends ApiResponse
{
    private string $user;
    private int $numImages;
    private DateTime $lastModified;

    public function __construct(string $user, int $numImages, DateTime $lastModified)
    {
        $this->user = $user;
        $this->numImages = $numImages;
        $this->lastModified = $lastModified;
    }

    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array{user:string,numImages:int,lastModified:string} */
        $body = Utils::convertResponseToArray($response);
        return new self($body['user'], $body['numImages'], new DateTime($body['lastModified']));
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getNumImages(): int
    {
        return $this->numImages;
    }

    public function getLastModified(): DateTime
    {
        return $this->lastModified;
    }

    protected function getArrayOffsets(): array
    {
        return [
            'user' => fn (): string => $this->getUser(),
            'numImages' => fn (): int => $this->getNumImages(),
            'lastModified' => fn (): DateTime => $this->getLastModified(),
        ];
    }
}
