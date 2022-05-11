<?php declare(strict_types=1);
namespace ImboClient\Response;

use ImboClient\Exception\InvalidResponseBodyException;
use ImboClient\Utils;
use Psr\Http\Message\ResponseInterface;

class Stats extends ApiResponse
{
    private int $numImages;
    private int $numUsers;
    private int $numBytes;
    private array $customStats;

    public function __construct(int $numImages, int $numUsers, int $numBytes, array $customStats = [])
    {
        $this->numImages = $numImages;
        $this->numUsers = $numUsers;
        $this->numBytes = $numBytes;
        $this->customStats = $customStats;
    }

    /**
     * @throws InvalidResponseBodyException
     */
    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array{numImages:int,numUsers:int,numBytes:int,custom:array} */
        $body = Utils::convertResponseToArray($response);
        return new self($body['numImages'], $body['numUsers'], $body['numBytes'], $body['custom']);
    }

    public function getNumImages(): int
    {
        return $this->numImages;
    }

    public function getNumUsers(): int
    {
        return $this->numUsers;
    }

    public function getNumBytes(): int
    {
        return $this->numBytes;
    }

    public function getCustomStats(): array
    {
        return $this->customStats;
    }

    protected function getArrayOffsets(): array
    {
        return [
            'numImages' => fn (): int => $this->getNumImages(),
            'numUsers' => fn (): int => $this->getNumUsers(),
            'numBytes' => fn (): int => $this->getNumBytes(),
            'custom' => fn (): array => $this->getCustomStats(),
        ];
    }
}
