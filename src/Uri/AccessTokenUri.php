<?php declare(strict_types=1);
namespace ImboClient\Uri;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;

class AccessTokenUri extends Uri
{
    private string $privateKey;

    public function __construct(string $uri, string $privateKey)
    {
        parent::__construct($uri);
        $this->privateKey = $privateKey;
    }

    public function __toString(): string
    {
        $uri = Utils::uriFor(parent::__toString());
        return (string) Uri::withQueryValue($uri, 'accessToken', hash_hmac('sha256', (string) $uri, $this->privateKey));
    }
}
