<?php declare(strict_types=1);

namespace ImboClient\Response;

use ImboClient\ImagesQuery;
use ImboClient\Response\Images\Image;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(Images::class)]
class ImagesTest extends TestCase
{
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
                    'mimeType' => 'image/png',
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
                    'mimeType' => 'image/png',
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
                    'mimeType' => 'image/png',
                    'extension' => 'png',
                    'metadata' => [],
                ],
            ],
        ];
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => json_encode($images),
            ]),
        ]);

        $query = new ImagesQuery();
        $imagesResponse = Images::fromHttpResponse($response, $query);
        $this->assertCount(3, $imagesResponse);
        foreach ($imagesResponse as $i => $image) {
            $this->assertSame($images['images'][$i]['imageIdentifier'], $image->getImageIdentifier());
        }

        /** @var ImagesQuery */
        $nextQuery = $imagesResponse->getNextQuery();
        $this->assertSame(2, $nextQuery->getPage());
        $this->assertSame(1, $imagesResponse->getPageInfo()->getPage());
    }

    public function testArrayAccess(): void
    {
        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
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
                            'mimeType' => 'image/png',
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
                            'mimeType' => 'image/png',
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
                            'mimeType' => 'image/png',
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
