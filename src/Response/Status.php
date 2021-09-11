<?php declare(strict_types=1);
namespace ImboClient\Response;

use DateTime;
use ImboClient\Exception\InvalidResponseBodyException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

class Status
{
    private DateTime $date;
    private bool $databaseStatus;
    private bool $storageStatus;

    public function __construct(DateTime $date, bool $databaseStatus, bool $storageStatus)
    {
        $this->date = $date;
        $this->databaseStatus = $databaseStatus;
        $this->storageStatus = $storageStatus;
    }

    /**
     * @throws InvalidResponseBodyException
     */
    public static function fromHttpResponse(ResponseInterface $response): self
    {
        try {
            /** @var array{date:string,database:bool,storage:bool} */
            $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidResponseBodyException('Invalid JSON in response body', $response, $e);
        }

        return new self(new DateTime($body['date']), $body['database'], $body['storage']);
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function isHealthy(): bool
    {
        return $this->isDatabaseHealthy() && $this->isStorageHealthy();
    }

    public function isDatabaseHealthy(): bool
    {
        return $this->databaseStatus;
    }

    public function isStorageHealthy(): bool
    {
        return $this->storageStatus;
    }
}
