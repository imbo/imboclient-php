<?php declare(strict_types=1);
namespace ImboClient\Exception;

use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Throwable;

class RequestException extends RuntimeException implements ClientException
{
    private RequestInterface $request;
    public function __construct(string $message, RequestInterface $request, Throwable $previous = null)
    {
        $this->request = $request;
        parent::__construct($message, 0, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
