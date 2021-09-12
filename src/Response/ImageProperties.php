<?php declare(strict_types=1);
namespace ImboClient\Response;

use Psr\Http\Message\ResponseInterface;

class ImageProperties extends Response
{
    private string $imageIdentifier;
    private int $originalSize;
    private int $originalWidth;
    private int $originalHeight;
    private string $originalMimeType;
    private string $originalExtension;

    public function __construct(string $imageIdentifier, int $originalSize, int $originalWidth, int $originalHeight, string $originalMimeType, string $originalExtension)
    {
        $this->imageIdentifier   = $imageIdentifier;
        $this->originalSize      = $originalSize;
        $this->originalWidth     = $originalWidth;
        $this->originalHeight    = $originalHeight;
        $this->originalMimeType  = $originalMimeType;
        $this->originalExtension = $originalExtension;
    }

    public static function fromHttpResponse(ResponseInterface $response): self
    {
        return new self(
            $response->getHeaderLine('x-imbo-imageidentifier'),
            (int) $response->getHeaderLine('x-imbo-originalfilesize'),
            (int) $response->getHeaderLine('x-imbo-originalwidth'),
            (int) $response->getHeaderLine('x-imbo-originalheight'),
            $response->getHeaderLine('x-imbo-originalmimetype'),
            $response->getHeaderLine('x-imbo-originalextension'),
        );
    }

    public function getImageIdentifier(): string
    {
        return $this->imageIdentifier;
    }

    public function getOriginalSize(): int
    {
        return $this->originalSize;
    }

    public function getOriginalWidth(): int
    {
        return $this->originalWidth;
    }

    public function getOriginalHeight(): int
    {
        return $this->originalHeight;
    }

    public function getOriginalMimeType(): string
    {
        return $this->originalMimeType;
    }

    public function getOriginalExtension(): string
    {
        return $this->originalExtension;
    }
}
