<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;

class Client
{
    /**
     * URLs to the Imbo server
     *
     * @var array<string>
     */
    private array $serverUrls = [];

    private string $user;
    private string $publicKey;
    private string $privateKey;
    private GuzzleHttpClient $httpClient;

    /**
     * Class constructor
     *
     * @param string|array<string> $imboUrls URLs to the Imbo installation
     * @param string $user User for imbo
     * @param string $publicKey Public key for user
     * @param string $privateKey Private key for user
     * @param GuzzleHttpClient $httpClient Pre-configured HTTP client
     */
    public function __construct($imboUrls, string $user, string $publicKey, string $privateKey, GuzzleHttpClient $httpClient = null)
    {
        if (!is_array($imboUrls)) {
            $imboUrls = [$imboUrls];
        }

        $this->imboUrls = $imboUrls;
        $this->user = $user;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;

        if (null === $httpClient) {
            $httpClient = new GuzzleHttpClient();
        }

        $this->httpClient = $httpClient;
    }
}
