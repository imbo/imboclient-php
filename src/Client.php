<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;
use ImboClient\Exception\ClientException;
use ImboClient\Exception\InvalidArgumentException;
use ImboClient\Exception\InvalidLocalFileException;
use ImboClient\Exception\RequestException;
use ImboClient\Exception\RuntimeException;
use ImboClient\Middleware\Authenticate;
use ImboClient\Response\AccessControlRule;
use ImboClient\Response\AccessControlRules;
use ImboClient\Response\AddedImage;
use ImboClient\Response\AddedShortUrl;
use ImboClient\Response\DeletedImage;
use ImboClient\Response\DeletedShortUrl;
use ImboClient\Response\DeletedShortUrls;
use ImboClient\Response\ImageProperties;
use ImboClient\Response\Images;
use ImboClient\Response\PublicKey;
use ImboClient\Response\ResourceGroup;
use ImboClient\Response\ResourceGroups;
use ImboClient\Response\Stats;
use ImboClient\Response\Status;
use ImboClient\Response\User;
use ImboClient\Url\AccessTokenUrl;
use ImboClient\Url\ImageUrl;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Client
{
    public const MAJOR_VERSION = 3;

    /** @var array<string> */
    private array $baseUrls;
    private string $user;
    private string $publicKey;
    private string $privateKey;
    private GuzzleHttpClient $httpClient;

    /**
     * Class constructor
     *
     * @param string|array<string> $baseUrls URL(s) to the Imbo server
     * @param string $user User for imbo
     * @param string $publicKey Public key for user
     * @param string $privateKey Private key for user
     * @param GuzzleHttpClient $httpClient Pre-configured HTTP client
     */
    public function __construct($baseUrls, string $user, string $publicKey, string $privateKey, GuzzleHttpClient $httpClient = null)
    {
        if (!is_array($baseUrls)) {
            $baseUrls = [$baseUrls];
        }

        $this->baseUrls = array_map(fn (string $url): string => rtrim($url, '/'), $baseUrls);
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
                $this->getUrlForPath('status.json'),
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
                $this->getUrlForPath('stats.json'),
            ),
        );
    }

    public function getUserInfo(): User
    {
        return User::fromHttpResponse(
            $this->getHttpResponse(
                $this->getAccessTokenUrlForPath('users/' . $this->user . '.json'),
            ),
        );
    }

    public function getImages(ImagesQuery $query = null): Images
    {
        $query = $query ?: new ImagesQuery();
        return Images::fromHttpResponse(
            $this->getHttpResponse(
                $this->getAccessTokenUrlForPath('users/' . $this->user . '/images.json'),
                [
                    'query' => $query->toArray(),
                ],
            ),
            $query,
        );
    }

    public function addImageFromString(string $blob): AddedImage
    {
        return AddedImage::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath('users/' . $this->user . '/images'),
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
                $this->getUrlForPath(
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
                $this->getUrlForPath(
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
                $this->getUrlForPath(
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
                $this->getUrlForPath(
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
                $this->getUrlForPath(
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
                $this->getUrlForPath(
                    'users/' . $this->user . '/images/' . $imageIdentifier . '/metadata',
                ),
                [],
                'DELETE',
                true,
            ),
        );
    }

    public function getImageUrl(string $imageIdentifier): ImageUrl
    {
        return new ImageUrl(
            $this->getHostForImageIdentifier($imageIdentifier) . '/users/' . $this->user . '/images/' . $imageIdentifier,
            $this->privateKey,
        );
    }

    public function addShortUrl(ImageUrl $imageUrl): AddedShortUrl
    {
        return AddedShortUrl::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath(
                    'users/' . $this->user . '/images/' . $imageUrl->getImageIdentifier() . '/shorturls',
                ),
                [
                    'json' => [
                        'user'            => $this->user,
                        'imageIdentifier' => $imageUrl->getImageIdentifier(),
                        'extension'       => $imageUrl->getExtension(),
                        'query'           => $imageUrl->getQuery() ?: null,
                    ],
                ],
                'POST',
                true,
            ),
        );
    }

    public function deleteImageShortUrls(string $imageIdentifier): DeletedShortUrls
    {
        return DeletedShortUrls::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath(
                    'users/' . $this->user . '/images/' . $imageIdentifier . '/shorturls',
                ),
                [],
                'DELETE',
                true,
            ),
        );
    }

    public function getShortUrlProperties(string $shortUrlId): ImageProperties
    {
        return ImageProperties::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath(
                    's/' . $shortUrlId,
                ),
                [],
                'HEAD',
            ),
        );
    }

    public function deleteShortUrl(string $shortUrlId): DeletedShortUrl
    {
        $properties = $this->getShortUrlProperties($shortUrlId);
        return DeletedShortUrl::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath(
                    'users/' . $this->user . '/images/' . $properties->getImageIdentifier() . '/shorturls/' . $shortUrlId,
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
        return $this->getImageDataFromUrl($this->getImageUrl($imageIdentifier));
    }

    /**
     * @throws RuntimeException
     */
    public function getImageDataFromUrl(ImageUrl $url): string
    {
        try {
            $blob = $this->httpClient->get($url)->getBody()->getContents();
        } catch (BadResponseException $e) {
            throw new RuntimeException('Unable to fetch file at URL: ' . $url, (int) $e->getCode(), $e);
        }

        return $blob;
    }

    /**
     * @param array<string> $resources
     * @throws InvalidArgumentException
     */
    public function addResourceGroup(string $name, array $resources = []): ResourceGroup
    {
        $this->validateResourceGroupName($name);

        if ($this->resourceGroupExists($name)) {
            throw new InvalidArgumentException('Resource group already exists');
        }

        return ResourceGroup::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath('groups'),
                [
                    'json' => [
                        'name'      => $name,
                        'resources' => $resources,
                    ],
                ],
                'POST',
                true,
            ),
        );
    }

    /**
     * @param array<string> $resources
     */
    public function updateResourceGroup(string $name, array $resources = []): ResourceGroup
    {
        $this->validateResourceGroupName($name);
        return ResourceGroup::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath('groups/' . $name),
                [
                    'json' => [
                        'resources' => $resources,
                    ],
                ],
                'PUT',
                true,
            ),
        );
    }

    public function deleteResourceGroup(string $name): ResourceGroup
    {
        $this->validateResourceGroupName($name);
        return ResourceGroup::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath('groups/' . $name),
                [],
                'DELETE',
                true,
            ),
        );
    }

    public function resourceGroupExists(string $name): bool
    {
        try {
            $this->getHttpResponse(
                $this->getAccessTokenUrlForPath('groups/' . $name),
                [
                    'query' => [
                        'publicKey' => $this->publicKey,
                    ],
                ],
                'HEAD',
            );
        } catch (ClientException $e) {
            if (404 === $e->getCode()) {
                return false;
            }

            throw $e;
        }

        return true;
    }


    public function getResourceGroup(string $name): ResourceGroup
    {
        $this->validateResourceGroupName($name);
        return ResourceGroup::fromHttpResponse(
            $this->getHttpResponse(
                $this->getAccessTokenUrlForPath('groups/' . $name),
                [
                    'query' => [
                        'publicKey' => $this->publicKey,
                    ],
                ],
            ),
        );
    }

    public function getResourceGroups(Query $query = null): ResourceGroups
    {
        $query = $query ?: new Query();
        return ResourceGroups::fromHttpResponse(
            $this->getHttpResponse(
                $this->getAccessTokenUrlForPath('groups'),
                [
                    'query' => array_merge(
                        $query->toArray(),
                        [
                            'publicKey' => $this->publicKey,
                        ],
                    ),
                ],
            ),
            $query,
        );
    }

    public function addPublicKey(string $publicKey, string $privateKey): PublicKey
    {
        $this->validatePublicKeyName($publicKey);

        if ($this->publicKeyExists($publicKey)) {
            throw new InvalidArgumentException('Public key already exists');
        }

        return PublicKey::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath('keys'),
                [
                    'json' => [
                        'publicKey'  => $publicKey,
                        'privateKey' => $privateKey,
                    ],
                ],
                'POST',
                true,
            ),
        );
    }

    public function updatePublicKey(string $publicKey, string $privateKey): PublicKey
    {
        $this->validatePublicKeyName($publicKey);

        if (!$this->publicKeyExists($publicKey)) {
            throw new InvalidArgumentException('Public key does not exist');
        }

        return PublicKey::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath('keys/' . $publicKey),
                [
                    'json' => [
                        'privateKey' => $privateKey,
                    ],
                ],
                'PUT',
                true,
            ),
        );
    }

    public function deletePublicKey(string $publicKey): PublicKey
    {
        $this->validatePublicKeyName($publicKey);

        if (!$this->publicKeyExists($publicKey)) {
            throw new InvalidArgumentException('Public key does not exist');
        }

        return PublicKey::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath('keys/' . $publicKey),
                [],
                'DELETE',
                true,
            ),
        );
    }

    public function publicKeyExists(string $publicKey): bool
    {
        $this->validatePublicKeyName($publicKey);

        try {
            $this->getHttpResponse(
                $this->getAccessTokenUrlForPath('keys/' . $publicKey),
                [
                    'query' => [
                        'publicKey' => $this->publicKey,
                    ],
                ],
                'HEAD',
            );
        } catch (ClientException $e) {
            if (404 === $e->getCode()) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    public function getAccessControlRules(string $publicKey): AccessControlRules
    {
        $this->validatePublicKeyName($publicKey);
        return AccessControlRules::fromHttpResponse(
            $this->getHttpResponse(
                $this->getAccessTokenUrlForPath('keys/' . $publicKey . '/access.json'),
                [
                    'query' => [
                        'publicKey' => $this->publicKey,
                    ],
                ],
                'GET',
            ),
        );
    }

    public function getAccessControlRule(string $publicKey, string $ruleId): AccessControlRule
    {
        $this->validatePublicKeyName($publicKey);
        return AccessControlRule::fromHttpResponse(
            $this->getHttpResponse(
                $this->getAccessTokenUrlForPath('keys/' . $publicKey . '/access/' . $ruleId . '.json'),
                [
                    'query' => [
                        'publicKey' => $this->publicKey,
                    ],
                ],
            ),
        );
    }

    public function addAccessControlRules(string $publicKey, array $rules): AccessControlRules
    {
        $this->validatePublicKeyName($publicKey);
        return AccessControlRules::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath('keys/' . $publicKey . '/access.json'),
                [
                    'json' => $rules,
                ],
                'POST',
                true,
            ),
        );
    }

    public function deleteAccessControlRule(string $publicKey, string $ruleId): AccessControlRule
    {
        $this->validatePublicKeyName($publicKey);
        return AccessControlRule::fromHttpResponse(
            $this->getHttpResponse(
                $this->getUrlForPath('keys/' . $publicKey . '/access/' . $ruleId . '.json'),
                [],
                'DELETE',
                true,
            ),
        );
    }

    private function getAccessTokenUrlForPath(string $path, string $baseUrl = null): AccessTokenUrl
    {
        return new AccessTokenUrl(
            ($baseUrl ?: $this->baseUrls[0]) . '/' . $path,
            $this->privateKey,
        );
    }

    private function getUrlForPath(string $path, string $baseUrl = null): UriInterface
    {
        return new Uri(
            ($baseUrl ?: $this->baseUrls[0]) . '/' . $path,
        );
    }

    /**
     * @param array<string,mixed> $options
     */
    private function getHttpResponse(UriInterface $url, array $options = [], string $method = 'GET', bool $requireSignature = false): ResponseInterface
    {
        try {
            return $this->httpClient->request(
                $method,
                $url,
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
        if (1 === count($this->baseUrls)) {
            return $this->baseUrls[0];
        }

        $dec = ord($imageIdentifier[-1]);
        return $this->baseUrls[$dec % count($this->baseUrls)];
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

    private function validateResourceGroupName(string $name): void
    {
        if (!preg_match('/^[a-z0-9_-]+$/', $name)) {
            throw new InvalidArgumentException(
                'Group name can only consist of: a-z, 0-9 and the characters _ and -',
            );
        }
    }

    private function validatePublicKeyName(string $name): void
    {
        if (!preg_match('/^[a-z0-9_-]+$/', $name)) {
            throw new InvalidArgumentException(
                'Public key can only consist of: a-z, 0-9 and the characters _ and -',
            );
        }
    }
}
