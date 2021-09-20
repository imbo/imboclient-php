<?php declare(strict_types=1);
namespace ImboClient;

use ImboClient\Exception\InvalidResponseBodyException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

class Utils
{
    /**
     * @throws InvalidResponseBodyException
     */
    public static function convertResponseToArray(ResponseInterface $response): array
    {
        try {
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
