<?php declare(strict_types=1);
namespace ImboClient\Response\Images;

use ArrayObject;
use DateTime;

class Image
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
    private ArrayObject $metadata;

    public function __construct(string $imageIdentifier, string $checksum, string $originalChecksum, string $user, DateTime $added, DateTime $updated, int $size, int $width, int $height, string $mime, string $extension, ArrayObject $metadata)
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

    public function getMetadata(): ArrayObject
    {
        return $this->metadata;
    }
}
