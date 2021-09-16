<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;
use ImboClient\Exception\ClientException;
use ImboClient\Exception\InvalidLocalFileException;
use ImboClient\Exception\RequestException;
use ImboClient\Exception\RuntimeException;
use ImboClient\Middleware\Authenticate;
use ImboClient\Response\AddedImage;
use ImboClient\Response\AddedShortUri;
use ImboClient\Response\DeletedImage;
use ImboClient\Response\DeletedShortUri;
use ImboClient\Response\DeletedShortUris;
use ImboClient\Response\ImageProperties;
use ImboClient\Response\Images;
use ImboClient\Response\Stats;
use ImboClient\Response\Status;
use ImboClient\Response\User;
use ImboClient\Uri\AccessTokenUri;
use ImboClient\Uri\ImageUri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Client
{
    public const MAJOR_VERSION = 3;

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
            $httpClient = new GuzzleHttpClient([
                'handler' => $handler,
                'headers' => [
                    'User-Agent' => 'ImboClient/' . self::MAJOR_VERSION,
                ],
            ]);
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
                $this->getAccessTokenUriForPath('users/' . $this->user . '.json'),
            ),
        );
    }

    public function getImages(ImagesQuery $query = null): Images
    {
        $query = $query ?: new ImagesQuery();
        return Images::fromHttpResponse(
            $this->getHttpResponse(
                $this->getAccessTokenUriForPath(
                    'users/' . $this->user . '/images.json?' . http_build_query($query->toArray()),
                ),
            ),
            $query,
        );
    }

    public function addImageFromString(string $blob): AddedImage
    {
        return AddedImage::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath('users/' . $this->user . '/images'),
                [
                    'body' => $blob,
                ],
                'POST',
                true,
            ),
        );
    }

    public function addImageFromPath(string $path): AddedImage
    {
        $this->validateLocalFile($path);
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
            throw new RuntimeException('Unable to fetch file at URL: ' . $url, (int) $e->getCode(), $e);
        }

        return $this->addImageFromString($blob);
    }

    public function deleteImage(string $imageIdentifier): DeletedImage
    {
        return DeletedImage::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    'users/' . $this->user . '/images/' . $imageIdentifier,
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
                    'users/' . $this->user . '/images/' . $imageIdentifier,
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
                    'users/' . $this->user . '/images/' . $imageIdentifier . '/metadata.json',
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
                    'users/' . $this->user . '/images/' . $imageIdentifier . '/metadata',
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
                    'users/' . $this->user . '/images/' . $imageIdentifier . '/metadata',
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
                    'users/' . $this->user . '/images/' . $imageIdentifier . '/metadata',
                ),
                [],
                'DELETE',
                true,
            ),
        );
    }

    public function getImageUri(string $imageIdentifier): ImageUri
    {
        return new ImageUri(
            $this->getHostForImageIdentifier($imageIdentifier) . '/users/' . $this->user . '/images/' . $imageIdentifier,
            $this->privateKey,
        );
    }

    public function createShortUri(ImageUri $imageUri): AddedShortUri
    {
        return AddedShortUri::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    'users/' . $this->user . '/images/' . $imageUri->getImageIdentifier() . '/shorturls',
                ),
                [
                    'json' => [
                        'user'            => $this->user,
                        'imageIdentifier' => $imageUri->getImageIdentifier(),
                        'extension'       => $imageUri->getExtension(),
                        'query'           => $imageUri->getQuery() ?: null,
                    ],
                ],
                'POST',
                true,
            ),
        );
    }

    public function deleteImageShortUris(string $imageIdentifier): DeletedShortUris
    {
        return DeletedShortUris::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    'users/' . $this->user . '/images/' . $imageIdentifier . '/shorturls',
                ),
                [],
                'DELETE',
                true,
            ),
        );
    }

    public function getShortUriProperties(string $shortUriId): ImageProperties
    {
        return ImageProperties::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    's/' . $shortUriId,
                ),
                [],
                'HEAD',
            ),
        );
    }

    public function deleteShortUri(string $shortUriId): DeletedShortUri
    {
        $properties = $this->getShortUriProperties($shortUriId);
        return DeletedShortUri::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUriForPath(
                    'users/' . $this->user . '/images/' . $properties->getImageIdentifier() . '/shorturls/' . $shortUriId,
                ),
                [],
                'DELETE',
                true,
            ),
        );
    }

    public function imageExists(string $path): bool
    {
        $this->validateLocalFile($path);
        $checksum = md5_file($path);
        $query = new ImagesQuery();
        $images = $this->getImages(
            $query
                ->withOriginalChecksums([$checksum])
                ->withLimit(1),
        );

        return 0 < count($images);
    }

    public function imageIdentifierExists(string $imageIdentifier): bool
    {
        try {
            $this->getImageProperties($imageIdentifier);
        } catch (ClientException $e) {
            if (404 === $e->getCode()) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    public function getImageData(string $imageIdentifier): string
    {
        return $this->getImageDataFromUrl($this->getImageUri($imageIdentifier));
    }

    /**
     * @throws RuntimeException
     */
    public function getImageDataFromUrl(ImageUri $uri): string
    {
        try {
            $blob = $this->httpClient->get($uri)->getBody()->getContents();
        } catch (BadResponseException $e) {
            throw new RuntimeException('Unable to fetch file at URL: ' . $uri, (int) $e->getCode(), $e);
        }

        return $blob;
    }


    private function getAccessTokenUriForPath(string $path, string $baseUri = null): AccessTokenUri
    {
        return new AccessTokenUri(
            ($baseUri ?: $this->baseUris[0]) . '/' . $path,
            $this->privateKey,
        );
    }

    private function getUriForPath(string $path, string $baseUri = null): UriInterface
    {
        return new Uri(
            ($baseUri ?: $this->baseUris[0]) . '/' . $path,
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

    /**
     * @param string $path
     * @throws InvalidLocalFileException
     */
    private function validateLocalFile(string $path): void
    {
        if (!is_file($path)) {
            throw new InvalidLocalFileException('File does not exist: ' . $path);
        }

        if (!filesize($path)) {
            throw new InvalidLocalFileException('File is of zero length: ' . $path);
        }
    }
}
