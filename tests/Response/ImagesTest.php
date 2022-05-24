<?php declare(strict_types=1);
namespace ImboClient\Response;

use ImboClient\ImagesQuery;
use ImboClient\Response\Images\Image;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass ImboClient\Response\Images
 */
class ImagesTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::fromHttpResponse
     * @covers ::count
     * @covers ::rewind
     * @covers ::current
     * @covers ::key
     * @covers ::next
     * @covers ::valid
     * @covers ::getNextQuery
     * @covers ::getPageInfo
     */
    public function testCanCreateFromResponse(): void
    {
        $images = [
            'search' => [
                'hits' => 100,
                'page' => 1,
                'limit' => 3,
                'count' => 3,
            ],
            'images' => [
                [
                    'imageIdentifier' => 'image-id-1',
                    'checksum' => 'checksum-1',
                    'originalChecksum' => 'original-checksum-1',
                    'user' => 'user-1',
                    'added' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                    'updated' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                    'size' => 123,
                    'width' => 456,
                    'height' => 789,
                    'mime' => 'image/png',
                    'extension' => 'png',
                    'metadata' => [],
                ],
                [
                    'imageIdentifier' => 'image-id-2',
                    'checksum' => 'checksum-2',
                    'originalChecksum' => 'original-checksum-2',
                    'user' => 'user-2',
                    'added' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                    'updated' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                    'size' => 123,
                    'width' => 456,
                    'height' => 789,
                    'mime' => 'image/png',
                    'extension' => 'png',
                    'metadata' => [],
                ],
                [
                    'imageIdentifier' => 'image-id-3',
                    'checksum' => 'checksum-3',
                    'originalChecksum' => 'original-checksum-3',
                    'user' => 'user-3',
                    'added' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                    'updated' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                    'size' => 123,
                    'width' => 456,
                    'height' => 789,
                    'mime' => 'image/png',
                    'extension' => 'png',
                    'metadata' => [],
                ],
            ],
        ];
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => json_encode($images),
            ]),
        ]);

        $query = new ImagesQuery();
        $imagesResponse = Images::fromHttpResponse($response, $query);
        $this->assertSame(3, count($imagesResponse));
        foreach ($imagesResponse as $i => $image) {
            $this->assertSame($images['images'][$i]['imageIdentifier'], $image->getImageIdentifier());
        }

        /** @var ImagesQuery */
        $nextQuery = $imagesResponse->getNextQuery();
        $this->assertSame(2, $nextQuery->getPage());
        $this->assertSame(1, $imagesResponse->getPageInfo()->getPage());
    }

    /**
     * @covers ::offsetExists
     * @covers ::offsetGet
     * @covers ::getArrayOffsets
     */
    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                'getContents' => json_encode([
                    'search' => [
                        'hits' => 100,
                        'page' => 1,
                        'limit' => 3,
                        'count' => 3,
                    ],
                    'images' => [
                        [
                            'imageIdentifier' => 'image-id-1',
                            'checksum' => 'checksum-1',
                            'originalChecksum' => 'original-checksum-1',
                            'user' => 'user-1',
                            'added' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                            'updated' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                            'size' => 123,
                            'width' => 456,
                            'height' => 789,
                            'mime' => 'image/png',
                            'extension' => 'png',
                            'metadata' => [],
                        ],
                        [
                            'imageIdentifier' => 'image-id-2',
                            'checksum' => 'checksum-2',
                            'originalChecksum' => 'original-checksum-2',
                            'user' => 'user-2',
                            'added' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                            'updated' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                            'size' => 123,
                            'width' => 456,
                            'height' => 789,
                            'mime' => 'image/png',
                            'extension' => 'png',
                            'metadata' => [],
                        ],
                        [
                            'imageIdentifier' => 'image-id-3',
                            'checksum' => 'checksum-3',
                            'originalChecksum' => 'original-checksum-3',
                            'user' => 'user-3',
                            'added' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                            'updated' => 'Mon, 10 Dec 2012 11:57:51 GMT',
                            'size' => 123,
                            'width' => 456,
                            'height' => 789,
                            'mime' => 'image/png',
                            'extension' => 'png',
                            'metadata' => [],
                        ],
                    ],
                ]),
            ]),
        ]);
        $query = new ImagesQuery();
        $imagesResponse = Images::fromHttpResponse($response, $query);

        $this->assertArrayHasKey('images', $imagesResponse);
        $this->assertArrayNotHasKey('foobar', $imagesResponse);

        /** @var array<Image> */
        $images = $imagesResponse['images'];

        $this->assertSame('image-id-1', $images[0]->getImageIdentifier());
        $this->assertSame('image-id-2', $images[1]->getImageIdentifier());
        $this->assertSame('image-id-3', $images[2]->getImageIdentifier());
    }
}
