<?php declare(strict_types=1);
namespace ImboClient\Url;

use ImboClient\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass ImboClient\Url\ImageUrl
 */
class ImageUrlTest extends TestCase
{
    private ImageUrl $url;

    protected function setUp(): void
    {
        $this->url = new ImageUrl('http://imbo/users/user/images/id', 'private key');
    }

    /**
     * @covers ::__construct
     */
    public function testThrowsExceptionOnInvalidUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing image identifier');
        new ImageUrl('invalid url', 'private key');
    }

    /**
     * @return array<string,array{url:string,expectedImageIdentifier:string,expectedExtension:?string}>
     */
    public function getUrls(): array
    {
        return [
            'no extension' => [
                'url'                     => 'https://imbo/users/user/images/image-identifier',
                'expectedImageIdentifier' => 'image-identifier',
                'expectedExtension'       => null,
            ],
            'with extension' => [
                'url'                     => 'https://imbo/users/user/images/image-identifier.png',
                'expectedImageIdentifier' => 'image-identifier',
                'expectedExtension'       => 'png',
            ],
        ];
    }

    /**
     * @dataProvider getUrls
     * @covers ::__construct
     * @covers ::getImageIdentifier
     * @covers ::getExtension
     */
    public function testCanGetImageIdentifierAndExtension(string $url, string $expectedImageIdentifier, string $expectedExtension = null): void
    {
        $url = new ImageUrl($url, 'private key');
        $this->assertSame($expectedImageIdentifier, $url->getImageIdentifier());
        $this->assertSame($expectedExtension, $url->getExtension());
    }

    /**
     * @return array<string,array{method:string,params:array,query:string}>
     */
    public function getTransformations(): array
    {
        return [
            'autoRotate' => [
                'method' => 'autoRotate',
                'params' => [],
                'query'  => 't[0]=autoRotate',
            ],
            'blur' => [
                'method' => 'blur',
                'params' => [['radius' => 1, 'sigma' => 2]],
                'query'  => 't[0]=blur:radius=1,sigma=2',
            ],
            'blur (type:motion)' => [
                'method' => 'blur',
                'params' => [['type' => 'motion', 'angle' => 10, 'radius' => 1, 'sigma' => 2]],
                'query'  => 't[0]=blur:type=motion,radius=1,sigma=2,angle=10',
            ],
            'blur (type:radial)' => [
                'method' => 'blur',
                'params' => [['type' => 'radial', 'angle' => 10]],
                'query'  => 't[0]=blur:type=radial,angle=10',
            ],
            'border (no params)' => [
                'method' => 'border',
                'params' => [],
                'query'  => 't[0]=border:color=000000,width=1,height=1,mode=outbound',
            ],
            'border (with params)' => [
                'method' => 'border',
                'params' => ['ffffff', 2, 3, 'inset'],
                'query'  => 't[0]=border:color=ffffff,width=2,height=3,mode=inset',
            ],
            'compress (no params)' => [
                'method' => 'compress',
                'params' => [],
                'query'  => 't[0]=compress:level=75',
            ],
            'compress (with params)' => [
                'method' => 'compress',
                'params' => [80],
                'query'  => 't[0]=compress:level=80',
            ],
            'desaturate' => [
                'method' => 'desaturate',
                'params' => [],
                'query'  => 't[0]=desaturate',
            ],
            'flipHorizontally' => [
                'method' => 'flipHorizontally',
                'params' => [],
                'query'  => 't[0]=flipHorizontally',
            ],
            'flipVertically' => [
                'method' => 'flipVertically',
                'params' => [],
                'query'  => 't[0]=flipVertically',
            ],
            'progressive' => [
                'method' => 'progressive',
                'params' => [],
                'query'  => 't[0]=progressive',
            ],
            'strip' => [
                'method' => 'strip',
                'params' => [],
                'query'  => 't[0]=strip',
            ],
            'transpose' => [
                'method' => 'transpose',
                'params' => [],
                'query'  => 't[0]=transpose',
            ],
            'transverse' => [
                'method' => 'transverse',
                'params' => [],
                'query'  => 't[0]=transverse',
            ],
        ];
    }

    /**
     * @dataProvider getTransformations
     * @covers ::autoRotate
     * @covers ::blur
     * @covers ::border
     * @covers ::compress
     * @covers ::desaturate
     * @covers ::flipHorizontally
     * @covers ::flipVertically
     * @covers ::progressive
     * @covers ::strip
     * @covers ::transpose
     * @covers ::transverse
     * @covers ::withTransformation
     */
    public function testCanApplyTransformations(string $method, array $params, string $query): void
    {
        /** @var ImageUrl */
        $url = $this->url->$method(...$params);
        $this->assertNotSame($url, $this->url);
        $this->assertStringContainsString(
            $query,
            urldecode((string) $url),
        );
    }
}
