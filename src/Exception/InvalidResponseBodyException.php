<?php declare(strict_types=1);

namespace ImboClient\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class InvalidResponseBodyException extends RuntimeException implements ClientException
{
    private ResponseInterface $response;

    public function __construct(string $message, ResponseInterface $response, ?Throwable $previous = null)
    {
        $this->response = $response;

        parent::__construct($message, $response->getStatusCode(), $previous);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
