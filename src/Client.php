<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Uri;
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
use ImboClient\Uri\AccessTokenUri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Client
{
    /** @var array<string> */
    private array $baseUris;
    private string $user;
    private string $publicKey;
    private string $privateKey;
    private GuzzleHttpClient $httpClient;

    /**
     * Class constructor
     *
     * @param string|array<string> $baseUris URI(s) to the Imbo server
     * @param string $user User for imbo
     * @param string $publicKey Public key for user
     * @param string $privateKey Private key for user
     * @param GuzzleHttpClient $httpClient Pre-configured HTTP client
     */
    public function __construct($baseUris, string $user, string $publicKey, string $privateKey, GuzzleHttpClient $httpClient = null)
    {
        if (!is_array($baseUris)) {
            $baseUris = [$baseUris];
        }

        $this->baseUris = array_map(fn (string $uri): string => rtrim($uri, '/'), $baseUris);
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
                $this->getUriForPath('status.json'),
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
                $this->getUriForPath('stats.json'),
            ),
        );
    }

    public function getUserInfo(): User
    {
        return User::fromHttpResponse(
            $this->getHttpResponse(
                $this->getAccessTokenUriForPath(sprintf('users/%s.json', $this->user)),
            ),
        );
    }

    public function getImages(ImagesQuery $query = null): Images
    {
        $query = $query ?: new ImagesQuery();
        return Images::fromHttpResponse(
            $this->getHttpResponse(
                $this->getAccessTokenUriForPath(
                    sprintf('users/%s/images.json?%s', $this->user, Query::build($query->toArray())),
                ),
            ),
            $query,
        );
    }

    public function addImageFromString(string $blob): AddedImage
    {
        return AddedImage::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(sprintf('users/%s/images', $this->user)),
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
                    sprintf('users/%s/images/%s/metadata.json', $this->user, $imageIdentifier),
                    $this->getHostForImageIdentifier($imageIdentifier),
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

    private function getAccessTokenUriForPath(string $path, string $baseUri = null): AccessTokenUri
    {
        return new AccessTokenUri(
            sprintf('%s/%s', $baseUri ?: $this->baseUris[0], $path),
            $this->privateKey,
        );
    }

    private function getUriForPath(string $path, string $baseUri = null): UriInterface
    {
        return new Uri(
            sprintf('%s/%s', $baseUri ?: $this->baseUris[0], $path),
        );
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

    /**
     * Get a predictable hostname for the given image identifier
     *
     * @param string $imageIdentifier The image identifier
     * @return string
     */
    private function getHostForImageIdentifier(string $imageIdentifier): string
    {
        if (1 === count($this->baseUris)) {
            return $this->baseUris[0];
        }

        $dec = ord($imageIdentifier[-1]);
        return $this->baseUris[$dec % count($this->baseUris)];
    }
}
