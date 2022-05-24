<?php declare(strict_types=1);
namespace ImboClient\Response\Images;

use DateTime;
use ImboClient\Response\ApiResponse;

class Image extends ApiResponse
{
    private string $imageIdentifier;
    private string $checksum;
    private string $originalChecksum;
    private string $user;
    private DateTime $added;
    private DateTime $updated;
    private int $size;
    private int $width;
    private int $height;
    private string $mime;
    private string $extension;
    private array $metadata;

    public function __construct(string $imageIdentifier, string $checksum, string $originalChecksum, string $user, DateTime $added, DateTime $updated, int $size, int $width, int $height, string $mime, string $extension, array $metadata)
    {
        $this->imageIdentifier  = $imageIdentifier;
        $this->checksum         = $checksum;
        $this->originalChecksum = $originalChecksum;
        $this->user             = $user;
        $this->added            = $added;
        $this->updated          = $updated;
        $this->size             = $size;
        $this->width            = $width;
        $this->height           = $height;
        $this->mime             = $mime;
        $this->extension        = $extension;
        $this->metadata         = $metadata;
    }

    public function getImageIdentifier(): string
    {
        return $this->imageIdentifier;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function getOriginalChecksum(): string
    {
        return $this->originalChecksum;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getAdded(): DateTime
    {
        return $this->added;
    }

    public function getUpdated(): DateTime
    {
        return $this->updated;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    protected function getArrayOffsets(): array
    {
        return [
            'imageIdentifier' => fn (): string => $this->getImageIdentifier(),
            'checksum' => fn (): string => $this->getChecksum(),
            'originalChecksum' => fn (): string => $this->getOriginalChecksum(),
            'user' => fn (): string => $this->getUser(),
            'added' => fn (): DateTime => $this->getAdded(),
            'updated' => fn (): DateTime => $this->getUpdated(),
            'size' => fn (): int => $this->getSize(),
            'width' => fn (): int => $this->getWidth(),
            'height' => fn (): int => $this->getHeight(),
            'mime' => fn (): string => $this->getMime(),
            'extension' => fn (): string => $this->getExtension(),
            'metadata' => fn (): array => $this->getMetadata(),
        ];
    }
}
