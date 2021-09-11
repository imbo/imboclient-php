<?php declare(strict_types=1);
namespace ImboClient\Response;

use ImboClient\Exception\InvalidResponseBodyException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * @throws InvalidResponseBodyException
     */
    protected static function convertResponseToArray(ResponseInterface $response): array
    {
        try {
            /** @var mixed */
            $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidResponseBodyException('Invalid JSON in response body', $response, $e);
        }

        if (!is_array($result)) {
            throw new InvalidResponseBodyException('Expected JSON array in response body', $response);
        }

        return $result;
    }
}
