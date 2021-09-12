<?php declare(strict_types=1);
namespace ImboClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use ImboClient\Exception\InvalidLocalFileException;
use ImboClient\Exception\RequestException;
use ImboClient\Exception\RuntimeException;
use ImboClient\Middleware\AccessToken;
use ImboClient\Middleware\Authenticate;
use ImboClient\Response\AddedImage;
use ImboClient\Response\DeletedImage;
use ImboClient\Response\ImageProperties;
use ImboClient\Response\Images;
use ImboClient\Response\Stats;
use ImboClient\Response\Status;
use ImboClient\Response\User;
use Psr\Http\Message\ResponseInterface;

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
            $handler->push(new AccessToken($this->privateKey));
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
            $response = $this->getHttpResponse('status.json');
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
            $this->getHttpResponse('stats.json'),
        );
    }

    public function getUserInfo(): User
    {
        return User::fromHttpResponse(
            $this->getHttpResponse(sprintf('users/%s.json', $this->user)),
        );
    }

    public function getImages(ImagesQuery $query = null): Images
    {
        if (null === $query) {
            $query = new ImagesQuery();
        }

        $queryAsArray = array_filter($query->toArray());

        return Images::fromHttpResponse(
            $this->getHttpResponse(
                sprintf('users/%s/images.json', $this->user),
                array_filter(
                    [
                        'query' => $queryAsArray,
                    ],
                ),
            ),
            $query,
        );
    }

    public function addImageFromString(string $blob): AddedImage
    {
        return AddedImage::fromHttpResponse(
            $this->getHttpResponse(
                sprintf('users/%s/images', $this->user),
                [
                    'body' => $blob,
                ],
                'POST',
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
                sprintf('users/%s/images/%s', $this->user, $imageIdentifier),
                [],
                'DELETE',
            ),
        );
    }

    private function getUriForPath(string $path): string
    {
        return sprintf(
            '%s/%s',
            rtrim($this->serverUrl, '/'),
            ltrim($path, '/'),
        );
    }

    public function getImageProperties(string $imageIdentifier): ImageProperties
    {
        return ImageProperties::fromHttpResponse(
            $this->getHttpResponse(
                sprintf('users/%s/images/%s', $this->user, $imageIdentifier),
                [],
                'HEAD',
            ),
        );
    }

    /**
     * @param array<string,mixed> $options
     */
    private function getHttpResponse(string $path, array $options = [], string $method = 'GET'): ResponseInterface
    {
        try {
            return $this->httpClient->request(
                $method,
                $this->getUriForPath($path),
                $options,
            );
        } catch (BadResponseException $e) {
            throw new RequestException('Imbo request failed', $e->getRequest(), $e);
        }
    }
}
