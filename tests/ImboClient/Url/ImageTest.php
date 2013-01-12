<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Url;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ImageTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Image
     */
    private $url;

    /**
     * @var string
     */
    private $baseUrl = 'http://host';

    /**
     * @var string
     */
    private $publicKey = 'key';

    /**
     * @var string
     */
    private $privateKey = '41ebdff96ee9986119a5033f30d9a6c8';

    /**
     * @var string
     */
    private $imageIdentifier = '3aea3926533f3c7b87d5500789aa2a17';

    /**
     * Set up the image URL instance
     *
     * @covers ImboClient\Url\Image::__construct
     */
    public function setUp() {
        $this->url = new Image(
            $this->baseUrl,
            $this->publicKey,
            $this->privateKey,
            $this->imageIdentifier
        );
    }

    /**
     * Tear down the image URL instance
     */
    public function tearDown() {
        $this->url = null;
    }

    /**
     * The image URL must be able to apply the border transformation with no custom parameters
     *
     * @covers ImboClient\Url\Image::border
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheBorderTransformationUsingDefaultParameters() {
        $this->assertSame($this->url, $this->url->border());
        $this->assertContains(
            '?t[]=' . urlencode('border:color=000000,width=1,height=1'),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the border transformation with custom parameters
     *
     * @covers ImboClient\Url\Image::border
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheBorderTransformationUsingCustomParameters() {
        $this->assertSame($this->url, $this->url->border('fff', 2, 3));
        $this->assertContains(
            '?t[]=' . urlencode('border:color=fff,width=2,height=3'),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the compress transformation with no custom parameters
     *
     * @covers ImboClient\Url\Image::compress
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheCompressTransformationUsingDefaultParameters() {
        $this->assertSame($this->url, $this->url->compress());
        $this->assertContains('?t[]=' . urlencode('compress:quality=75'), $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the compress transformation with custom parameters
     *
     * @covers ImboClient\Url\Image::compress
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheCompressTransformationUsingCustomParameters() {
        $this->assertSame($this->url, $this->url->compress(42));
        $this->assertContains('?t[]=' . urlencode('compress:quality=42'), $this->url->getUrl());
    }

    /**
     * Get different extensions
     *
     * @return array[]
     */
    public function getExtensions() {
        return array(
            array('jpg'),
            array('png'),
            array('gif'),
        );
    }

    /**
     * The image URL must be able to apply the convert transformation using custom extensions
     *
     * @dataProvider getExtensions
     * @covers ImboClient\Url\Image::convert
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheConvertTransformationUsingACustomExtension($extension) {
        $this->assertSame($this->url, $this->url->convert($extension));
        $this->assertStringStartsWith(
            sprintf(
                '%s/users/%s/images/%s.%s',
                $this->baseUrl,
                $this->publicKey,
                $this->imageIdentifier,
                $extension
            ),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the convert transformation using the gif() convenience
     * method
     *
     * @covers ImboClient\Url\Image::gif
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheConvertTransformationUsingTheGifConvenienceMethod() {
        $this->assertSame($this->url, $this->url->gif());
        $this->assertStringStartsWith(
            sprintf(
                '%s/users/%s/images/%s.gif',
                $this->baseUrl,
                $this->publicKey,
                $this->imageIdentifier
            ),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the convert transformation using the jpg() convenience
     * method
     *
     * @covers ImboClient\Url\Image::jpg
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheConvertTransformationUsingTheJpgConvenienceMethod() {
        $this->assertSame($this->url, $this->url->jpg());
        $this->assertStringStartsWith(
            sprintf(
                '%s/users/%s/images/%s.jpg',
                $this->baseUrl,
                $this->publicKey,
                $this->imageIdentifier
            ),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the convert transformation using the png() convenience
     * method
     *
     * @covers ImboClient\Url\Image::png
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheConvertTransformationUsingThePngConvenienceMethod() {
        $this->assertSame($this->url, $this->url->png());
        $this->assertStringStartsWith(
            sprintf(
                '%s/users/%s/images/%s.png',
                $this->baseUrl,
                $this->publicKey,
                $this->imageIdentifier
            ),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the crop transformation with parameters
     *
     * @covers ImboClient\Url\Image::crop
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheCropTransformationWithParameters() {
        $this->assertSame($this->url, $this->url->crop(1, 2, 3, 4));
        $this->assertStringStartsWith(
            sprintf(
                '%s/users/%s/images/%s?t[]=%s',
                $this->baseUrl,
                $this->publicKey,
                $this->imageIdentifier,
                urlencode('crop:x=1,y=2,width=3,height=4')
            ),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the flipHorizontally transformation
     *
     * @covers ImboClient\Url\Image::flipHorizontally
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheFliphorizontallyTransformation() {
        $this->assertSame($this->url, $this->url->flipHorizontally());
        $this->assertContains('?t[]=flipHorizontally', $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the flipVertically transformation
     *
     * @covers ImboClient\Url\Image::flipVertically
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheFlipverticallyTransformation() {
        $this->assertSame($this->url, $this->url->flipVertically());
        $this->assertContains('?t[]=flipVertically', $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the transpose transformation
     *
     * @covers ImboClient\Url\Image::transpose
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheTransposeTransformation() {
        $this->assertSame($this->url, $this->url->transpose());
        $this->assertContains('?t[]=transpose', $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the transverse transformation
     *
     * @covers ImboClient\Url\Image::transverse
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheTransverseTransformation() {
        $this->assertSame($this->url, $this->url->transverse());
        $this->assertContains('?t[]=transverse', $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the desaturate transformation
     *
     * @covers ImboClient\Url\Image::desaturate
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheDesaturateTransformation() {
        $this->assertSame($this->url, $this->url->desaturate());
        $this->assertContains('?t[]=desaturate', $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the resize transformation using only width
     *
     * @covers ImboClient\Url\Image::resize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheResizeTransformationUsingOnlyWidth() {
        $this->assertSame($this->url, $this->url->resize(100));
        $this->assertContains('?t[]=' . urlencode('resize:width=100'), $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the resize transformation using only height
     *
     * @covers ImboClient\Url\Image::resize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheResizeTransformationUsingOnlyHeight() {
        $this->assertSame($this->url, $this->url->resize(null, 100));
        $this->assertContains('?t[]=' . urlencode('resize:height=100'), $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the resize transformation using both width and height
     *
     * @covers ImboClient\Url\Image::resize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheResizeTransformationUsingBothWidthAndHeight() {
        $this->assertSame($this->url, $this->url->resize(1, 2));
        $this->assertContains('?t[]=' . urlencode('resize:width=1,height=2'), $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the maxSize transformation using only width
     *
     * @covers ImboClient\Url\Image::maxSize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheMaxsizeTransformationUsingOnlyWidth() {
        $this->assertSame($this->url, $this->url->maxSize(100));
        $this->assertContains('?t[]=' . urlencode('maxSize:width=100'), $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the maxSize transformation using only height
     *
     * @covers ImboClient\Url\Image::maxSize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheMaxsizeTransformationUsingOnlyHeight() {
        $this->assertSame($this->url, $this->url->maxSize(200, 100));
        $this->assertContains('?t[]=' . urlencode('maxSize:width=200,height=100'), $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the maxSize transformation using both width and height
     *
     * @covers ImboClient\Url\Image::maxSize
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheMaxsizeTransformationUsingBothWidthAndHeight() {
        $this->assertSame($this->url, $this->url->maxSize(1, 2));
        $this->assertContains('?t[]=' . urlencode('maxSize:width=1,height=2'), $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the rotate transformation using only angle
     *
     * @covers ImboClient\Url\Image::rotate
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheRotateTransformationUsingOnlyAngle() {
        $this->assertSame($this->url, $this->url->rotate(42));
        $this->assertContains('?t[]=' . urlencode('rotate:angle=42,bg=000000'), $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the rotate transformation using angle and bg
     *
     * @covers ImboClient\Url\Image::rotate
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheRotateTransformationUsingAngleAndBackground() {
        $this->assertSame($this->url, $this->url->rotate(42, 'fff'));
        $this->assertContains('?t[]=' . urlencode('rotate:angle=42,bg=fff'), $this->url->getUrl());
    }

    /**
     * The image URL must be able to apply the thumbnail transformation using the default parameters
     *
     * @covers ImboClient\Url\Image::thumbnail
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheThumbnailTransformationUsingDefaultParameters() {
        $this->assertSame($this->url, $this->url->thumbnail());
        $this->assertContains(
            '?t[]=' . urlencode('thumbnail:width=50,height=50,fit=outbound'),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the thumbnail transformation using custom parameters
     *
     * @covers ImboClient\Url\Image::thumbnail
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheThumbnailTransformationUsingCustomParameters() {
        $this->assertSame($this->url, $this->url->thumbnail(1, 2, 'inset'));
        $this->assertContains(
            '?t[]=' . urlencode('thumbnail:width=1,height=2,fit=inset'),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the canvas transformation using only required parameters
     *
     * @covers ImboClient\Url\Image::canvas
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheCanvasTransformationUsingOnlyRequiredParameters() {
        $this->assertSame($this->url, $this->url->canvas(100, 200));
        $this->assertContains(
            '?t[]=' . urlencode('canvas:width=100,height=200'),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to apply the canvas transformation using all parameters
     *
     * @covers ImboClient\Url\Image::canvas
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanApplyTheCanvasTransformationUsingAllParameters() {
        $this->assertSame($this->url, $this->url->canvas(100, 200, 'free', 10, 20, '000'));
        $this->assertContains(
            '?t[]=' . urlencode('canvas:width=100,height=200,mode=free,x=10,y=20,bg=000'),
            $this->url->getUrl()
        );
    }

    /**
     * Data provider for testGetUrl()
     *
     * @return array
     */
    public function getUrlData() {
        return array(
            array('http://imbo', 'publicKey', 'image', 'http://imbo/users/publicKey/images/image'),
        );
    }

    /**
     * The image URL must be able to generate a complete URL with an access token appended
     *
     * @dataProvider getUrlData
     * @covers ImboClient\Url\Url::getUrl
     * @covers ImboClient\Url\Image::getResourceUrl
     */
    public function testCanGenerateACompleteUrlIncludingAnAccessToken($host, $publicKey, $imageIdentifier, $expected) {
        $url = new Image($host, $publicKey, 'privateKey', $imageIdentifier);
        $this->assertStringStartsWith($expected, $url->getUrl());
        $this->assertRegExp('/accessToken=[a-f0-9]{64}$/', $url->getUrl());
    }

    /**
     * The image URL must be able to reset all applied transformations
     *
     * @covers ImboClient\Url\Image::reset
     * @covers ImboClient\Url\Image::gif
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testCanResetAllAppliedTransformations() {
        $this->url->gif();
        $this->assertStringStartsWith(
            sprintf(
                '%s/users/%s/images/%s.gif?',
                $this->baseUrl,
                $this->publicKey,
                $this->imageIdentifier
            ),
            $this->url->getUrl()
        );

        $this->url->reset();

        $this->assertStringStartsWith(
            sprintf(
                '%s/users/%s/images/%s?',
                $this->baseUrl,
                $this->publicKey,
                $this->imageIdentifier
            ),
            $this->url->getUrl()
        );
    }

    /**
     * The image URL must be able to chain all available transformations
     *
     * @covers ImboClient\Url\Image::border
     * @covers ImboClient\Url\Image::compress
     * @covers ImboClient\Url\Image::convert
     * @covers ImboClient\Url\Image::crop
     * @covers ImboClient\Url\Image::flipHorizontally
     * @covers ImboClient\Url\Image::flipVertically
     * @covers ImboClient\Url\Image::resize
     * @covers ImboClient\Url\Image::maxSize
     * @covers ImboClient\Url\Image::rotate
     * @covers ImboClient\Url\Image::thumbnail
     * @covers ImboClient\Url\Image::canvas
     * @covers ImboClient\Url\Image::transpose
     * @covers ImboClient\Url\Image::transverse
     * @covers ImboClient\Url\Image::desaturate
     */
    public function testCanChainAllTransformations() {
        $url = $this->url->border()
                         ->compress()
                         ->convert('png')
                         ->crop(1, 1, 40, 40)
                         ->flipHorizontally()
                         ->flipVertically()
                         ->resize(200)
                         ->maxSize(100)
                         ->rotate(90)
                         ->thumbnail()
                         ->canvas(300, 300)
                         ->transpose()
                         ->transverse()
                         ->desaturate()
                         ->getUrl();

        $this->assertStringStartsWith(
            sprintf(
                '%s/users/%s/images/%s.png?t[]=%s&t[]=%s&t[]=%s&t[]=%s&t[]=%s&t[]=%s&t[]=%s&' .
                't[]=%s&t[]=%s&t[]=%s&t[]=%s&t[]=%s&t[]=%s&accessToken=',

                $this->baseUrl, $this->publicKey, $this->imageIdentifier,
                urlencode('border:color=000000,width=1,height=1'),
                urlencode('compress:quality=75'),
                urlencode('crop:x=1,y=1,width=40,height=40'),
                urlencode('flipHorizontally'),
                urlencode('flipVertically'),
                urlencode('resize:width=200'),
                urlencode('maxSize:width=100'),
                urlencode('rotate:angle=90,bg=000000'),
                urlencode('thumbnail:width=50,height=50,fit=outbound'),
                urlencode('canvas:width=300,height=300'),
                urlencode('transpose'),
                urlencode('transverse'),
                urlencode('desaturate')
            ),
            $url
        );
    }
}
