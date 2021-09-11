<?php declare(strict_types=1);
namespace ImboClient\Response;

use ArrayObject;
use ImboClient\Exception\InvalidResponseBodyException;
use Psr\Http\Message\ResponseInterface;

class Stats extends Response
{
    private int $numImages;
    private int $numUsers;
    private int $numBytes;
    private ArrayObject $customStats;

    public function __construct(int $numImages, int $numUsers, int $numBytes, ArrayObject $customStats)
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
        $body = self::convertResponseToArray($response);
        return new self($body['numImages'], $body['numUsers'], $body['numBytes'], new ArrayObject($body['custom']));
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

    public function getCustomStats(): ?ArrayObject
    {
        return $this->customStats;
    }
}
