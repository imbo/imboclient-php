<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\HandlerStack;
use ImboClient\Middleware\AccessToken;
use ImboClient\Middleware\Authenticate;

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
     * @param string|array<string> $serverUrls URLs to the Imbo installation
     * @param string $user User for imbo
     * @param string $publicKey Public key for user
     * @param string $privateKey Private key for user
     * @param GuzzleHttpClient $httpClient Pre-configured HTTP client
     */
    public function __construct($serverUrls, string $user, string $publicKey, string $privateKey, GuzzleHttpClient $httpClient = null)
    {
        if (!is_array($serverUrls)) {
            $serverUrls = [$serverUrls];
        }

        $this->serverUrls = $serverUrls;
        $this->user = $user;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;

        if (null === $httpClient) {
            $handler = HandlerStack::create();
            $handler->push(new AccessToken($this->privateKey));
            $handler->push(new Authenticate($this->publicKey, $this->privateKey));
            $httpClient = new GuzzleHttpClient(['handler' => $handler]);
        }

        $this->httpClient = $httpClient;
    }
}
