<?php declare(strict_types=1);

namespace ImboClient\Response;

use DateTime;
use ImboClient\Response\Images\Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Image::class)]
class ImageTest extends TestCase
{
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
        $this->assertSame('image/png', $image->getMimeType());
        $this->assertSame('png', $image->getExtension());
        $this->assertSame(['some' => 'data'], $image->getMetadata());
    }

    public function testArrayAccess(): void
    {
        $image = new Image('image-id', 'checksum', 'original-checksum', 'user', new DateTime('Mon, 20 Sep 2021 20:33:57 GMT'), new DateTime('Mon, 20 Sep 2021 20:33:58 GMT'), 1, 2, 3, 'image/png', 'png', ['some' => 'data']);
        $this->assertArrayHasKey('imageIdentifier', $image);
        $this->assertArrayHasKey('checksum', $image);
        $this->assertArrayHasKey('originalChecksum', $image);
        $this->assertArrayHasKey('user', $image);
        $this->assertArrayHasKey('added', $image);
        $this->assertArrayHasKey('updated', $image);
        $this->assertArrayHasKey('size', $image);
        $this->assertArrayHasKey('width', $image);
        $this->assertArrayHasKey('height', $image);
        $this->assertArrayHasKey('mimeType', $image);
        $this->assertArrayHasKey('extension', $image);
        $this->assertArrayHasKey('metadata', $image);
        $this->assertArrayNotHasKey('foobar', $image);

        /** @var DateTime */
        $added = $image['added'];

        /** @var DateTime */
        $updated = $image['updated'];

        $this->assertSame('image-id', $image['imageIdentifier']);
        $this->assertSame('checksum', $image['checksum']);
        $this->assertSame('original-checksum', $image['originalChecksum']);
        $this->assertSame('user', $image['user']);
        $this->assertSame(1632170037, $added->getTimestamp());
        $this->assertSame(1632170038, $updated->getTimestamp());
        $this->assertSame(1, $image['size']);
        $this->assertSame(2, $image['width']);
        $this->assertSame(3, $image['height']);
        $this->assertSame('image/png', $image['mimeType']);
        $this->assertSame('png', $image['extension']);
        $this->assertSame(['some' => 'data'], $image['metadata']);
    }
}
