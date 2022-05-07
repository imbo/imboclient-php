<?php declare(strict_types=1);
namespace ImboClient\Response;

use ArrayAccess;
use ImboClient\Exception\RuntimeException;
use ImboClient\Utils;
use Psr\Http\Message\ResponseInterface;

class AddedImage extends ApiResponse implements ArrayAccess
{
    private string $imageIdentifier;
    private int $width;
    private int $height;
    private string $extension;

    public function __construct(string $imageIdentifier, int $width, int $height, string $extension)
    {
        $this->imageIdentifier = $imageIdentifier;
        $this->width           = $width;
        $this->height          = $height;
        $this->extension       = $extension;
    }

    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array{imageIdentifier:string,width:int,height:int,extension:string} */
        $body = Utils::convertResponseToArray($response);
        $addedImage = new self($body['imageIdentifier'], $body['width'], $body['height'], $body['extension']);
        return $addedImage->withResponse($response);
    }

    public function getImageIdentifier(): string
    {
        return $this->imageIdentifier;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function offsetExists($offset): bool
    {
        return
            'imageIdentifier' === $offset ||
            'width' === $offset ||
            'height' === $offset ||
            'extension' === $offset;
    }

    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'imageIdentifier': return $this->getImageIdentifier();
            case 'width': return $this->getWidth();
            case 'height': return $this->getHeight();
            case 'extension': return $this->getExtension();
            default: return null;
        }
    }

    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('Not supported');
    }

    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Not supported');
    }
}
