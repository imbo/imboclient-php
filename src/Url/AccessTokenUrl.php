<?php declare(strict_types=1);

namespace ImboClient\Url;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;

class AccessTokenUrl extends Uri
{
    private string $privateKey;

    public function __construct(string $url, string $privateKey)
    {
        parent::__construct($url);
        $this->privateKey = $privateKey;
    }

    public function __toString(): string
    {
        $url = Utils::uriFor(parent::__toString());

        return (string) Uri::withQueryValue($url, 'accessToken', hash_hmac('sha256', (string) $url, $this->privateKey));
    }
}
