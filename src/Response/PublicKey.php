<?php declare(strict_types=1);

namespace ImboClient\Response;

use ImboClient\Exception\InvalidResponseBodyException;
use ImboClient\Utils;
use Psr\Http\Message\ResponseInterface;

class PublicKey extends ApiResponse
{
    private string $publicKey;

    public function __construct(string $publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @throws InvalidResponseBodyException
     */
    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array{publicKey:string} */
        $body = Utils::convertResponseToArray($response);

        return (new self($body['publicKey']))->withResponse($response);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    protected function getArrayOffsets(): array
    {
        return [
            'publicKey' => fn (): string => $this->getPublicKey(),
        ];
    }
}
