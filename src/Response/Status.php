<?php declare(strict_types=1);
namespace ImboClient\Response;

use DateTime;
use ImboClient\Utils;
use Psr\Http\Message\ResponseInterface;

class Status extends ApiResponse
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

    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array{date:string,database:bool,storage:bool} */
        $body = Utils::convertResponseToArray($response);
        $status = new self(new DateTime($body['date']), $body['database'], $body['storage']);
        return $status->withResponse($response);
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

    protected function getArrayOffsets(): array
    {
        $response = $this->getResponse();
        return [
            'date' => fn (): DateTime => $this->getDate(),
            'database' => fn (): bool => $this->isDatabaseHealthy(),
            'storage' => fn (): bool => $this->isStorageHealthy(),
            'status' => fn (): ?int => $response ? $response->getStatusCode() : null,
            'message' => fn (): ?string => $response ? $response->getReasonPhrase() : null,
        ];
    }
}
