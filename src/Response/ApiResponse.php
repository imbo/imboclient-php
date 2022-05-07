<?php declare(strict_types=1);
namespace ImboClient\Response;

use Psr\Http\Message\ResponseInterface;

abstract class ApiResponse
{
    private ?ResponseInterface $response;

    /**
     * @return static
     */
    public function withResponse(ResponseInterface $response): self
    {
        $clone = clone $this;
        $clone->response = $response;
        return $clone;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
