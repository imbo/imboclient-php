<?php declare(strict_types=1);
namespace ImboClient\Response;

use DateTime;
use ImboClient\Response\Images\Image;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass ImboClient\Response\Images\Image
 */
class ImageTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getImageIdentifier
     * @covers ::getChecksum
     * @covers ::getOriginalChecksum
     * @covers ::getUser
     * @covers ::getAdded
     * @covers ::getUpdated
     * @covers ::getSize
     * @covers ::getWidth
     * @covers ::getHeight
     * @covers ::getMime
     * @covers ::getExtension
     * @covers ::getMetadata
     */
    public function testImageAccessors(): void
    {
        $image = new Image('image-id', 'checksum', 'original-checksum', 'user', $added = new DateTime('now'), $updated = new DateTime('now'), 1, 2, 3, 'image/png', 'png', ['some' => 'data']);
        $this->assertSame('image-id', $image->getImageIdentifier());
        $this->assertSame('checksum', $image->getChecksum());
        $this->assertSame('original-checksum', $image->getOriginalChecksum());
        $this->assertSame('user', $image->getUser());
        $this->assertSame($added, $image->getAdded());
        $this->assertSame($updated, $image->getUpdated());
        $this->assertSame(1, $image->getSize());
        $this->assertSame(2, $image->getWidth());
        $this->assertSame(3, $image->getHeight());
        $this->assertSame('image/png', $image->getMime());
        $this->assertSame('png', $image->getExtension());
        $this->assertSame(['some' => 'data'], $image->getMetadata());
    }
}
