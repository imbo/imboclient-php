<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use ImboClient\Exception\InvalidLocalFileException;
use ImboClient\Exception\RequestException;
use ImboClient\Exception\RuntimeException;
use ImboClient\Middleware\Authenticate;
use ImboClient\Response\AddedImage;
use ImboClient\Response\DeletedImage;
use ImboClient\Response\ImageProperties;
use ImboClient\Response\Images;
use ImboClient\Response\Stats;
use ImboClient\Response\Status;
use ImboClient\Response\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Client
{
    private string $serverUrl;
    private string $user;
    private string $publicKey;
    private string $privateKey;
    private GuzzleHttpClient $httpClient;

    /**
     * Class constructor
     *
     * @param string $serverUrl URL to the Imbo server
     * @param string $user User for imbo
     * @param string $publicKey Public key for user
     * @param string $privateKey Private key for user
     * @param GuzzleHttpClient $httpClient Pre-configured HTTP client
     */
    public function __construct(string $serverUrl, string $user, string $publicKey, string $privateKey, GuzzleHttpClient $httpClient = null)
    {
        $this->serverUrl = $serverUrl;
        $this->user = $user;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;

        if (null === $httpClient) {
            $handler = HandlerStack::create();
            $handler->push(new Authenticate($this->publicKey, $this->privateKey));
            $httpClient = new GuzzleHttpClient(['handler' => $handler]);
        }

        $this->httpClient = $httpClient;
    }

    /**
     * @throws RequestException
     */
    public function getServerStatus(): Status
    {
        try {
            $response = $this->getHttpResponse(
                $this->getUriForPath(
                    'status.json',
                    false,
                ),
            );
        } catch (RequestException $e) {
            $previous = $e->getPrevious();

            if (!$previous instanceof ServerException) {
                throw $e;
            }

            $response = $previous->getResponse();
        }

        return Status::fromHttpResponse($response);
    }

    public function getServerStats(): Stats
    {
        return Stats::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    'stats.json',
                    false,
                ),
            ),
        );
    }

    public function getUserInfo(): User
    {
        return User::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    sprintf('users/%s.json', $this->user),
                ),
            ),
        );
    }

    public function getImages(ImagesQuery $query = null): Images
    {
        $query = $query ?? new ImagesQuery();

        $path = sprintf('users/%s/images.json', $this->user);
        $queryString = http_build_query($query->toArray());

        return Images::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    sprintf('%s?%s', $path, $queryString),
                    true,
                ),
            ),
            $query,
        );
    }

    public function addImageFromString(string $blob): AddedImage
    {
        return AddedImage::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    sprintf('users/%s/images', $this->user),
                    false,
                ),
                [
                    'body' => $blob,
                ],
                'POST',
                true,
            ),
        );
    }

    /**
     * @throws InvalidLocalFileException
     */
    public function addImageFromPath(string $path): AddedImage
    {
        if (!is_file($path)) {
            throw new InvalidLocalFileException(sprintf('File does not exist: %s', $path));
        }

        if (!filesize($path)) {
            throw new InvalidLocalFileException(sprintf('File is of zero length: %s', $path));
        }

        return $this->addImageFromString(file_get_contents($path));
    }

    /**
     * @throws RuntimeException
     */
    public function addImageFromUrl(string $url): AddedImage
    {
        try {
            $blob = $this->httpClient->get($url)->getBody()->getContents();
        } catch (BadResponseException $e) {
            throw new RuntimeException(sprintf('Unable to fetch file at URL: %s', $url), (int) $e->getCode(), $e);
        }

        return $this->addImageFromString($blob);
    }

    public function deleteImage(string $imageIdentifier): DeletedImage
    {
        return DeletedImage::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    sprintf('users/%s/images/%s', $this->user, $imageIdentifier),
                    false,
                ),
                [],
                'DELETE',
                true,
            ),
        );
    }

    public function getImageProperties(string $imageIdentifier): ImageProperties
    {
        return ImageProperties::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    sprintf('users/%s/images/%s', $this->user, $imageIdentifier),
                ),
                [],
                'HEAD',
            ),
        );
    }

    public function getMetadata(string $imageIdentifier): array
    {
        return Utils::convertResponseToArray(
            $this->getHttpResponse(
                $this->getUriForPath(
                    sprintf('users/%s/images/%s/metadata', $this->user, $imageIdentifier),
                ),
            ),
        );
    }

    public function setMetadata(string $imageIdentifier, array $metadata): array
    {
        return Utils::convertResponseToArray(
            $this->getHttpResponse(
                $this->getUriForPath(
                    sprintf('users/%s/images/%s/metadata', $this->user, $imageIdentifier),
                    false,
                ),
                [
                    'json' => $metadata,
                ],
                'PUT',
                true,
            ),
        );
    }

    public function updateMetadata(string $imageIdentifier, array $metadata): array
    {
        return Utils::convertResponseToArray(
            $this->getHttpResponse(
                $this->getUriForPath(
                    sprintf('users/%s/images/%s/metadata', $this->user, $imageIdentifier),
                    false,
                ),
                [
                    'json' => $metadata,
                ],
                'POST',
                true,
            ),
        );
    }

    public function deleteMetadata(string $imageIdentifier): array
    {
        return Utils::convertResponseToArray(
            $this->getHttpResponse(
                $this->getUriForPath(
                    sprintf('users/%s/images/%s/metadata', $this->user, $imageIdentifier),
                ),
                [],
                'DELETE',
                true,
            ),
        );
    }

    private function getUriForPath(string $path, bool $withAccessToken = true): UriInterface
    {
        $uri = Psr7\Utils::uriFor(sprintf(
            '%s/%s',
            rtrim($this->serverUrl, '/'),
            ltrim($path, '/'),
        ));

        if ($withAccessToken) {
            $uri = Psr7\Uri::withQueryValue($uri, 'accessToken', hash_hmac('sha256', (string) $uri, $this->privateKey));
        }

        return $uri;
    }

    /**
     * @param array<string,mixed> $options
     */
    private function getHttpResponse(UriInterface $uri, array $options = [], string $method = 'GET', bool $requireSignature = false): ResponseInterface
    {
        try {
            return $this->httpClient->request(
                $method,
                $uri,
                array_merge(
                    $options,
                    [
                        'require_imbo_signature' => $requireSignature,
                    ],
                ),
            );
        } catch (BadResponseException $e) {
            throw new RequestException('Imbo request failed', $e->getRequest(), $e);
        }
    }
}
