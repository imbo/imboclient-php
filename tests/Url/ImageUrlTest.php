<?php declare(strict_types=1);
namespace ImboClient\Url;

use ImboClient\Exception\InvalidArgumentException;
use ImboClient\Exception\InvalidImageTransformationException;
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
     * @dataProvider getTransformations
     * @covers ::autoRotate
     * @covers ::blur
     * @covers ::border
     * @covers ::canvas
     * @covers ::compress
     * @covers ::contrast
     * @covers ::convert
     * @covers ::crop
     * @covers ::desaturate
     * @covers ::drawPois
     * @covers ::extremeSharpen
     * @covers ::flipHorizontally
     * @covers ::flipVertically
     * @covers ::gif
     * @covers ::histogram
     * @covers ::jpg
     * @covers ::level
     * @covers ::maxSize
     * @covers ::moderateSharpen
     * @covers ::modulate
     * @covers ::resize
     * @covers ::png
     * @covers ::progressive
     * @covers ::rotate
     * @covers ::sepia
     * @covers ::sharpen
     * @covers ::smartSize
     * @covers ::strip
     * @covers ::strongSharpen
     * @covers ::thumbnail
     * @covers ::transpose
     * @covers ::transverse
     * @covers ::vignette
     * @covers ::watermark
     * @covers ::withTransformation
     */
    public function testCanApplyTransformations(string $method, array $params, string $query = null, string $pathSuffix = null): void
    {
        /** @var ImageUrl */
        $url = $this->url->$method(...$params);
        $this->assertNotSame($url, $this->url);

        if ($query) {
            $this->assertStringContainsString($query, urldecode((string) $url));
        }

        if ($pathSuffix) {
            $this->assertStringEndsWith($pathSuffix, $url->getPath());
        }
    }

    /**
     * @covers ::blur
     */
    public function testBlurCanValidateParams(): void
    {
        $this->expectException(InvalidImageTransformationException::class);
        $this->expectExceptionMessage('radius must be specified');
        $this->url->blur(['type' => 'motion']);
    }

    /**
     * @covers ::canvas
     */
    public function testCanvasCanValidateParams(): void
    {
        $this->expectException(InvalidImageTransformationException::class);
        $this->expectExceptionMessage('width and height must be positive');
        $this->url->canvas(0, 1);
    }

    /**
     * @covers ::convert
     */
    public function testConvertThrowsExceptionOnUnsupportedExtension(): void
    {
        $this->expectException(InvalidImageTransformationException::class);
        $this->expectExceptionMessage('Extension bmp is not supported');
        $this->url->convert('bmp');
    }

    /**
     * @dataProvider getInvalidCropParams
     * @covers ::crop
     */
    public function testCropCanValidateParams(int $width, int $height, int $x = null, int $y = null, string $mode = null, string $expectedExceptionMessage): void
    {
        $this->expectException(InvalidImageTransformationException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->url->crop($width, $height, $x, $y, $mode);
    }

    /**
     * @covers ::maxSize
     */
    public function testMaxSizeCanValidateParams(): void
    {
        $this->expectException(InvalidImageTransformationException::class);
        $this->expectExceptionMessage('width and/or height must be specified');
        $this->url->maxSize();
    }

    /**
     * @covers ::modulate
     */
    public function testModulateCanValidateParams(): void
    {
        $this->expectException(InvalidImageTransformationException::class);
        $this->expectExceptionMessage('brightness, saturation and/or hue must be specified');
        $this->url->modulate();
    }

    /**
     * @covers ::resize
     */
    public function testResizeCanValidateParams(): void
    {
        $this->expectException(InvalidImageTransformationException::class);
        $this->expectExceptionMessage('width and/or height must be specified');
        $this->url->resize();
    }

    /**
     * @covers ::rotate
     */
    public function testRotateCanValidateParams(): void
    {
        $this->expectException(InvalidImageTransformationException::class);
        $this->expectExceptionMessage('angle must be positive');
        $this->url->rotate(-10);
    }

    /**
     * @covers ::smartSize
     */
    public function testSmartsizeCanValidateParams(): void
    {
        $this->expectException(InvalidImageTransformationException::class);
        $this->expectExceptionMessage('width and height must be positive');
        $this->url->smartSize(100, 0);
    }

    /**
     * @covers ::reset
     */
    public function testCanResetUrl(): void
    {
        $url = $this->url
            ->thumbnail()
            ->png()
            ->reset();

        $this->assertStringEndsWith('/id', $url->getPath());
        $this->assertEmpty($url->getQuery());
    }

    /**
     * @return array<string,array{url:string,expectedImageIdentifier:string,expectedExtension:?string}>
     */
    public static function getUrls(): array
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
     * @return array<string,array{method:string,params:array,query:?string,pathSuffix?:string}>
     */
    public static function getTransformations(): array
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
            'canvas' => [
                'method' => 'canvas',
                'params' => [100, 200, 'free', 1, 2, 'ffffff'],
                'query'  => 't[0]=canvas:width=100,height=200,mode=free,x=1,y=2,bg=ffffff',
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
            'contrast' => [
                'method' => 'contrast',
                'params' => [1.1, 0.9],
                'query'  => 't[0]=contrast:alpha=1.1,beta=0.9',
            ],
            'contrast (large beta)' => [
                'method' => 'contrast',
                'params' => [1.1, 1.2],
                'query'  => 't[0]=contrast:alpha=1.1,beta=1',
            ],
            'convert (gif)' => [
                'method'     => 'convert',
                'params'     => ['gif'],
                'query'      => null,
                'pathSuffix' => '/id.gif',
            ],
            'convert (jpg)' => [
                'method'     => 'convert',
                'params'     => ['jpg'],
                'query'      => null,
                'pathSuffix' => '/id.jpg',
            ],
            'convert (png)' => [
                'method'     => 'convert',
                'params'     => ['png'],
                'query'      => null,
                'pathSuffix' => '/id.png',
            ],
            'crop' => [
                'method' => 'crop',
                'params' => [100, 200, 3, 4, 'center-x'],
                'query'  => 't[0]=crop:width=100,height=200,x=3,y=4,mode=center-x',
            ],
            'desaturate' => [
                'method' => 'desaturate',
                'params' => [],
                'query'  => 't[0]=desaturate',
            ],
            'drawPois' => [
                'method' => 'drawPois',
                'params' => ['ffffff', 2, 3],
                'query'  => 't[0]=drawPois:color=ffffff,borderSize=2,pointSize=3',
            ],
            'extremeSharpen' => [
                'method' => 'extremeSharpen',
                'params' => [],
                'query'  => 't[0]=sharpen:extreme',
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
            'gif' => [
                'method'     => 'gif',
                'params'     => [],
                'query'      => null,
                'pathSuffix' => '/id.gif',
            ],
            'histogram (no params)' => [
                'method' => 'histogram',
                'params' => [],
                'query'  => 'histogram',
            ],
            'histogram (all params)' => [
                'method' => 'histogram',
                'params' => [2, 3.3, 'f00', '0f0', '00f'],
                'query'  => 't[0]=histogram:scale=2,ratio=3.3,red=f00,green=0f0,blue=00f',
            ],
            'jpg' => [
                'method'     => 'jpg',
                'params'     => [],
                'query'      => null,
                'pathSuffix' => '/id.jpg',
            ],
            'level' => [
                'method' => 'level',
                'params' => [2, 'rg'],
                'query' => 't[0]=level:amount=2,channel=rg',
            ],
            'maxSize' => [
                'method' => 'maxSize',
                'params' => [100, 200],
                'query'  => 't[0]=maxSize:width=100,height=200',
            ],
            'moderateSharpen' => [
                'method' => 'moderateSharpen',
                'params' => [],
                'query'  => 't[0]=sharpen:moderate',
            ],
            'modulate' => [
                'method' => 'modulate',
                'params' => [2, 3, 4],
                'query'  => 't[0]=modulate:b=2,s=3,h=4',
            ],
            'png' => [
                'method'     => 'png',
                'params'     => [],
                'query'      => null,
                'pathSuffix' => '/id.png',
            ],
            'progressive' => [
                'method' => 'progressive',
                'params' => [],
                'query'  => 't[0]=progressive',
            ],
            'resize' => [
                'method' => 'resize',
                'params' => [200, 300],
                'query'  => 't[0]=resize:width=200,height=300',
            ],
            'rotate' => [
                'method' => 'rotate',
                'params' => [45, 'ffffff'],
                'query'  => 't[0]=rotate:angle=45,bg=ffffff',
            ],
            'sepia' => [
                'method' => 'sepia',
                'params' => [75],
                'query'  => 't[0]=sepia:threshold=75',
            ],
            'sharpen (no params)' => [
                'method' => 'sharpen',
                'params' => [],
                'query'  => 't[0]=sharpen',
            ],
            'sharpen (all params)' => [
                'method' => 'sharpen',
                'params' => [1, 2, 3, 4],
                'query'  => 't[0]=sharpen:radius=1,sigma=2,gain=3,threshold=4',
            ],
            'smartSize' => [
                'method' => 'smartSize',
                'params' => [100, 200, 'close', '5,6'],
                'query'  => 't[0]=smartSize:width=100,height=200,crop=close,poi=5,6',
            ],
            'strip' => [
                'method' => 'strip',
                'params' => [],
                'query'  => 't[0]=strip',
            ],
            'strongSharpen' => [
                'method' => 'strongSharpen',
                'params' => [],
                'query'  => 't[0]=sharpen:strong',
            ],
            'thumbnail (no params)' => [
                'method' => 'thumbnail',
                'params' => [],
                'query'  => 't[0]=thumbnail:width=50,height=50,fit=outbound',
            ],
            'thumbnail (all params)' => [
                'method' => 'thumbnail',
                'params' => [75, 60, 'inset'],
                'query'  => 't[0]=thumbnail:width=75,height=60,fit=inset',
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
            'vignette (no params)' => [
                'method' => 'vignette',
                'params' => [],
                'query'  => 't[0]=vignette',
            ],
            'vignette (all params)' => [
                'method' => 'vignette',
                'params' => [2, 'fff', 'f00'],
                'query'  => 't[0]=vignette:scale=2,outer=fff,inner=f00',
            ],
            'watermark' => [
                'method' => 'watermark',
                'params' => ['some-id', 100, 200, 'top-left', 2, 3],
                'query'  => 't[0]=watermark:position=top-left,x=2,y=3,img=some-id,width=100,height=200',
            ],
        ];
    }

    /**
     * @return array<string,array{width:int,height:int,x:?int,y:?int,mode:?string,expectedExceptionMessage:string}>
     */
    public static function getInvalidCropParams(): array
    {
        return [
            'no crop mode' => [
                'width'                    => 100,
                'height'                   => 100,
                'x'                        => null,
                'y'                        => null,
                'mode'                     => null,
                'expectedExceptionMessage' => 'x and y needs to be specified without a crop mode',
            ],
            'center-x' => [
                'width'                    => 100,
                'height'                   => 100,
                'x'                        => 1,
                'y'                        => null,
                'mode'                     => 'center-x',
                'expectedExceptionMessage' => 'y needs to be specified when mode is center-x',
            ],
            'center-y' => [
                'width'                    => 100,
                'height'                   => 100,
                'x'                        => null,
                'y'                        => 1,
                'mode'                     => 'center-y',
                'expectedExceptionMessage' => 'x needs to be specified when mode is center-y',
            ],
        ];
    }
}
