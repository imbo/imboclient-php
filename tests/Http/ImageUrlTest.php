<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClientTest\Http;

use ImboClient\Http\ImageUrl,
    InvalidArgumentException;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers ImboClient\Http\ImageUrl
 */
class ImageUrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ImageUrl
     */
    private $url;

    /**
     * Base URL
     *
     * @var string
     */
    private $baseUrl = 'http://imbo/users/christer/images/image';

    /**
     * Set up the image URL instance
     */
    public function setUp() {
        $this->url = ImageUrl::factory($this->baseUrl);
    }

    /**
     * Tear down the image URL instance
     */
    public function tearDown() {
        $this->url = null;
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getTransformations() {
        return array(
            'autoRotate' => array(
                'autoRotate',
                null,
                'autoRotate',
            ),
            'blur' => array(
                'blur',
                array(array('mode' => 'adaptive', 'radius' => 3, 'sigma' => 1.4)),
                'blur:mode=adaptive,radius=3,sigma=1.4'
            ),
            'blur (motion)' => array(
                'blur',
                array(array('mode' => 'motion', 'radius' => 3, 'sigma' => 1.4, 'angle' => 13.3)),
                'blur:mode=motion,radius=3,sigma=1.4,angle=13.3'
            ),
            'blur (radial)' => array(
                'blur',
                array(array('mode' => 'radial', 'angle' => 180)),
                'blur:mode=radial,angle=180'
            ),
            'border' => array(
                'border',
                null,
                'border:color=000000,width=1,height=1,mode=outbound',
            ),
            'canvas' => array(
                'canvas',
                array(100, 200),
                'canvas:width=100,height=200',
            ),
            'canvas with all params' => array(
                'canvas',
                array(100, 200, 'center', 10, 20, 'fff'),
                'canvas:width=100,height=200,mode=center,x=10,y=20,bg=fff',
            ),
            'compress' => array(
                'compress',
                null,
                'compress:level=75',
            ),
            'crop' => array(
                'crop',
                array(1, 2, 3, 4),
                'crop:width=3,height=4,x=1,y=2',
            ),
            'crop with center mode' => array(
                'crop',
                array(null, null, 10, 20, 'center'),
                'crop:width=10,height=20,mode=center'
            ),
            'crop with center-x mode' => array(
                'crop',
                array(null, 2, 10, 20, 'center-x'),
                'crop:width=10,height=20,y=2,mode=center-x'
            ),
            'crop with center-y mode' => array(
                'crop',
                array(2, null, 10, 20, 'center-y'),
                'crop:width=10,height=20,x=2,mode=center-y'
            ),
            'contrast' => array(
                'contrast',
                null,
                'contrast'
            ),
            'contrast with parameters' => array(
                'contrast',
                array(4, 0.7),
                'contrast:alpha=4,beta=0.7'
            ),
            'desaturate' => array(
                'desaturate',
                null,
                'desaturate',
            ),
            'drawPois' => array(
                'drawPois',
                null,
                'drawPois'
            ),
            'drawPois with params' => array(
                'drawPois',
                array('bf1942', 3, 40),
                'drawPois:color=bf1942,borderSize=3,pointSize=40'
            ),
            'flipHorizontally' => array(
                'flipHorizontally',
                null,
                'flipHorizontally',
            ),
            'flipVertically' => array(
                'flipVertically',
                null,
                'flipVertically',
            ),
            'histogram' => array(
                'histogram',
                null,
                'histogram',
            ),
            'histogram with all params' => array(
                'histogram',
                array(2, 3.14, '#f00', '#0f0', '#00f'),
                'histogram:scale=2,ratio=3.14,red=#f00,green=#0f0,blue=#00f',
            ),
            'level' => array(
                'level',
                array(3, 'cm'),
                'level:amount=3,channel=cm',
            ),
            'maxSize with width' => array(
                'maxSize',
                array(100),
                'maxSize:width=100',
            ),
            'maxSize with height' => array(
                'maxSize',
                array(null, 100),
                'maxSize:height=100',
            ),
            'maxSize with width and height' => array(
                'maxSize',
                array(200, 100),
                'maxSize:width=200,height=100',
            ),
            'modulate with brightness' => array(
                'modulate',
                array(100),
                'modulate:b=100',
            ),
            'modulate with saturation' => array(
                'modulate',
                array(null, 100),
                'modulate:s=100',
            ),
            'modulate with hue' => array(
                'modulate',
                array(null, null, 100),
                'modulate:h=100',
            ),
            'modulate with all params' => array(
                'modulate',
                array(1, 2, 3),
                'modulate:b=1,s=2,h=3',
            ),
            'progressive' => array(
                'progressive',
                null,
                'progressive',
            ),
            'resize with width' => array(
                'resize',
                array(100),
                'resize:width=100',
            ),
            'resize with width' => array(
                'resize',
                array(null, 100),
                'resize:height=100',
            ),
            'resize with width and height' => array(
                'resize',
                array(200, 100),
                'resize:width=200,height=100',
            ),
            'rotate' => array(
                'rotate',
                array(75),
                'rotate:angle=75,bg=000000',
            ),
            'sepia' => array(
                'sepia',
                null,
                'sepia:threshold=80',
            ),
            'sharpen' => array(
                'sharpen',
                null,
                'sharpen'
            ),
            'sharpen with params' => array(
                'sharpen',
                array(array('preset' => 'strong', 'gain' => 5, 'sigma' => 3)),
                'sharpen:preset=strong,sigma=3,gain=5'
            ),
            'smartSize' => array(
                'smartSize',
                array(320, 240),
                'smartSize:width=320,height=240'
            ),
            'smartSize with explicit poi' => array(
                'smartSize',
                array(320, 230, null, '55,120'),
                'smartSize:width=320,height=230,poi=55,120'
            ),
            'smartSize with crop closeness' => array(
                'smartSize',
                array(320, 230, 'wide'),
                'smartSize:width=320,height=230,crop=wide'
            ),
            'strip' => array(
                'strip',
                null,
                'strip',
            ),
            'thumbnail' => array(
                'thumbnail',
                null,
                'thumbnail:width=50,height=50,fit=outbound',
            ),
            'transpose' => array(
                'transpose',
                null,
                'transpose',
            ),
            'transverse' => array(
                'transverse',
                null,
                'transverse',
            ),
            'vignette' => array(
                'vignette',
                null,
                'vignette'
            ),
            'vignette with params' => array(
                'vignette',
                array(1.7, 'bf1942', 'f00baa'),
                'vignette:scale=1.7,outer=bf1942,inner=f00baa'
            ),
            'watermark' => array(
                'watermark',
                null,
                'watermark:position=top-left,x=0,y=0',
            ),
            'watermark with all params' => array(
                'watermark',
                array('img', 40, 50, 'center', 1, 2),
                'watermark:position=center,x=1,y=2,img=img,width=40,height=50',
            ),
        );
    }

    /**
     * @dataProvider getTransformations
     */
    public function testCanApplyTransformationsToTheUrl($method, array $args = null, $query) {
        if ($args === null) {
            $this->assertSame($this->url, $this->url->$method());
        } else {
            $this->assertSame($this->url, call_user_func_array(array($this->url, $method), $args));
        }

        $uri = parse_url((string) $this->url);
        $queryString = substr($uri['query'], strpos($uri['query'], '=') + 1);

        $this->assertSame(urlencode($query), $queryString);
        $this->assertSame($this->url, $this->url->reset());
        $this->assertSame($this->baseUrl, (string) $this->url);
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getConvertTransformations() {
        return array(
            'convert' => array(
                'convert',
                array('png'),
                '.png',
            ),
            'gif conversion' => array(
                'gif',
                null,
                '.gif',
            ),
            'jpg conversion' => array(
                'jpg',
                null,
                '.jpg',
            ),
            'png conversion' => array(
                'png',
                null,
                '.png',
            ),
        );
    }

    /**
     * @dataProvider getConvertTransformations
     */
    public function testCanConvertToOtherFileExtensions($method, $args = null, $extension) {
        if ($args === null) {
            $this->assertSame($this->url, $this->url->$method());
        } else {
            $this->assertSame($this->url, call_user_func_array(array($this->url, $method), $args));
        }

        $this->assertStringEndsWith('image' . $extension, (string) $this->url);
        $this->assertSame($this->url, $this->url->reset());
        $this->assertSame($this->baseUrl, (string) $this->url);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage width and height must be specified
     */
    public function testCanvasMethodThrowExceptionOnMissingParameters() {
        $this->url->canvas(100, 0);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage width and height must be specified
     */
    public function testSmartSizeMethodThrowExceptionOnMissingParameters() {
        $this->url->smartSize(100, 0);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage `radius` must be specified
     */
    public function testBlurMethodThrowExceptionOnMissingParameters() {
        $this->url->blur(array());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage width and/or height must be specified
     */
    public function testMaxSizeMethodThrowExceptionOnMissingParameters() {
        $this->url->maxSize();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage brightness, saturation and/or hue must be specified
     */
    public function testModulateMethodThrowExceptionOnMissingParameters() {
        $this->url->modulate();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage width and/or height must be specified
     */
    public function testResizeMethodThrowExceptionOnMissingParameters() {
        $this->url->resize();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage angle must be specified
     */
    public function testRotateMethodThrowExceptionOnMissingParameters() {
        $this->url->rotate(0);
    }

    public function testCanAddMultipleTransformations() {
        $this->assertSame(
            'http://imbo/users/christer/images/image.jpg?t%5B0%5D=border%3Acolor%3D000000%2Cwidth%3D1%2Cheight%3D1%2Cmode%3Doutbound&t%5B1%5D=desaturate',
            (string) $this->url->border()->jpg()->desaturate()
        );
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getCropParams() {
        return array(
            'no crop mode and missing x and/or y value(s)' => array(
                null, null, 100, 100, null, 'x and y needs to be specified without a crop mode',
            ),
            'center-x mode with missing y parameter' => array(
                null, null, 100, 100, 'center-x', 'y needs to be specified when mode is center-x',
            ),
            'center-y mode with missing x parameter' => array(
                null, null, 100, 100, 'center-y', 'x needs to be specified when mode is center-y',
            ),
            'missing width' => array(
                0, 0, null, 100, null, 'width and height needs to be specified',
            ),
            'missing height' => array(
                0, 0, 100, null, null, 'width and height needs to be specified',
            ),
        );
    }

    /**
     * @dataProvider getCropParams
     */
    public function testValidatesCropParameters($x, $y, $width, $height, $mode, $exceptionMessage) {
        try {
            $this->url->crop($x, $y, $width, $height, $mode);
            $this->fail('Expected an exception');
        } catch (InvalidArgumentException $e) {
            $this->assertSame($exceptionMessage, $e->getMessage());
        }
    }

    /**
     * @see https://github.com/imbo/imboclient-php/issues/90
     */
    public function testUrlsCanGetConvertedToStringsMoreThanOnce() {
        $this->url->setPrivateKey('key');
        $this->url->maxSize(123, 123);

        $this->assertSame('http://imbo/users/christer/images/image?t%5B0%5D=maxSize%3Awidth%3D123%2Cheight%3D123&accessToken=ae738aa84615093e78c635fbbdbef4debb177346a956eb4cefe50bc83592da70', (string) $this->url);
        $this->assertSame((string) $this->url, (string) $this->url);
    }

    /**
     * @see https://github.com/imbo/imboclient-php/issues/91
     */
    public function testUrlsCanBePartiallyConvertedAndUpdated() {
        $expectedUrl = 'http://imbo/users/christer/images/image.png';

        $this->url->png();

        $this->assertSame($expectedUrl, (string) $this->url);
        $this->assertSame($expectedUrl, (string) $this->url);

        $this->url->desaturate();

        $expectedUrl .= '?t%5B0%5D=desaturate';

        $this->assertSame($expectedUrl, (string) $this->url);
        $this->assertSame($expectedUrl, (string) $this->url);
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getImageUrls() {
        return array(
            'no extension' => array('http://imbo/users/christer/images/image', 'christer', 'image'),
            'extension (jpg)' => array('http://imbo/users/christer/images/image.jpg', 'christer', 'image'),
            'extension (gif)' => array('http://imbo/users/christer/images/image.gif', 'christer', 'image'),
            'extension (png)' => array('http://imbo/users/christer/images/image.png', 'christer', 'image'),
            'URL with path prefix' => array('http://imbo/some_prefix/users/christer/images/image', 'christer', 'image'),
            'missing image identifier' => array('http://imbo/users/christer/images.json', 'christer', null),
            'URL with query params' => array('http://imbo/users/christer/images/image?t[]=thumbnail', 'christer', 'image'),
        );
    }

    /**
     * @dataProvider getImageUrls
     */
    public function testCanFetchTheUserAndTheImageIdentifierInTheUrl($url, $user, $imageIdentifier) {
        $imageUrl = ImageUrl::factory($url);
        $this->assertSame($user, $imageUrl->getUser(), 'Could not correctly identify the user in the URL');
        $this->assertSame($imageIdentifier, $imageUrl->getImageIdentifier(), 'Could not correctly identify the image identifier in the URL');
    }

    public function testCanGetTheImageExtension() {
        $this->assertNull($this->url->getExtension(), 'extension should initialy be null');

        $this->url->jpg();
        $this->assertSame('jpg', $this->url->getExtension(), 'Could not fetch extension after setting it to jpg');

        $this->url->png();
        $this->assertSame('png', $this->url->getExtension(), 'Could not fetch extension after setting it to png');

        $this->url->gif();
        $this->assertSame('gif', $this->url->getExtension(), 'Could not fetch extension after setting it to gif');
    }

    public function testCanReturnAddedTransformations() {
        $this->assertSame(array(), $this->url->getTransformations(), 'Transformations sould initially be an empty array');
        $this->url->thumbnail()->desaturate()->png();
        $this->assertSame(array(
            'thumbnail:width=50,height=50,fit=outbound',
            'desaturate',
        ), $this->url->getTransformations(), 'Could not fetch transformations after adding');

        $this->url->reset();
        $this->assertSame(array(), $this->url->getTransformations(), 'Resetting the URL did not clear the added transformations');
    }

    public function testCanRecreateUrlFromString() {
        $url  = 'http://imbo/users/foo-bar/images/imgIdentifier.jpg';
        $url .= '?t[0]=flipHorizontally';
        $url .= '&t[1]=sepia:threshold=30';
        $url .= '&publicKey=pub-key-generator';

        $imgUrl = ImageUrl::factory($url . '&accessToken=njkn12k4jbn124jk1n4jn13j4nk2341');

        $transformations = $imgUrl->getTransformations();
        $this->assertCount(2, $transformations);
        $this->assertSame('flipHorizontally', $transformations[0]);
        $this->assertSame('sepia:threshold=30', $transformations[1]);
        $this->assertSame('jpg', $imgUrl->getExtension());
        $this->assertSame('imgIdentifier', $imgUrl->getImageIdentifier());
        $this->assertSame('pub-key-generator', $imgUrl->getPublicKey());
        $this->assertSame('foo-bar', $imgUrl->getUser());

        $this->assertSame($url, rawurldecode((string) $imgUrl));
    }

    public function testCanRecreateUrlFromStringWithNonNumericTransformations() {
        $url  = 'http://imbo/users/foo-bar/images/imgIdentifier.jpg';
        $url .= '?t[]=flipHorizontally';
        $url .= '&t[]=sepia:threshold=30';
        $url .= '&publicKey=pub-key-generator';

        $imgUrl = ImageUrl::factory($url . '&accessToken=njkn12k4jbn124jk1n4jn13j4nk2341');

        $transformations = $imgUrl->getTransformations();
        $this->assertCount(2, $transformations);
        $this->assertSame('flipHorizontally', $transformations[0]);
        $this->assertSame('sepia:threshold=30', $transformations[1]);
        $this->assertSame('jpg', $imgUrl->getExtension());
        $this->assertSame('imgIdentifier', $imgUrl->getImageIdentifier());
        $this->assertSame('pub-key-generator', $imgUrl->getPublicKey());
        $this->assertSame('foo-bar', $imgUrl->getUser());

        $this->assertSame(
            'http://imbo/users/foo-bar/images/imgIdentifier.jpg?t[0]=flipHorizontally&t[1]=sepia:threshold=30&publicKey=pub-key-generator',
            rawurldecode((string) $imgUrl)
        );
    }

    public function testGeneratesAccessTokenIfPrivateKeyIsPassed() {
        $url  = 'http://imbo/users/foo-bar/images/imgIdentifier.jpg';
        $url .= '?t[]=flipHorizontally';
        $url .= '&t[]=sepia:threshold=30';
        $url .= '&publicKey=pub-key-generator';

        $imgUrl = ImageUrl::factory($url, 'privkey');
        $this->assertContains(
            '&accessToken=4b5b46a27669ae0fe8095e7fc1f0c1ba86c1231ba759ed8865a761f853bc08a3',
            (string) $imgUrl
        );
    }

    public function testOverridesPublicKeyIfNewPubKeyIsPassed() {
        $url  = 'http://imbo/users/foo-bar/images/imgIdentifier.jpg';
        $url .= '?t[]=flipHorizontally';
        $url .= '&t[]=sepia:threshold=30';
        $url .= '&publicKey=pub-key-generator';
        $url .= '&accessToken=foo';

        $imgUrl = ImageUrl::factory($url, 'privkey', 'pubkey');
        $this->assertNotContains('&publicKey=pub-key', (string) $imgUrl);
        $this->assertNotContains('&accessToken=foo', (string) $imgUrl);
        $this->assertContains(
            '&accessToken=8d9de618264f772df86a9b4f082ea00a3fa203bbc5644ae1d409d3c785806772',
            (string) $imgUrl
        );
    }

    public function testNormalizesUrlByRemovingRedundantPublicKey() {
        $url  = 'http://imbo/users/foo-bar/images/imgIdentifier.jpg';
        $url .= '?t[]=flipHorizontally';
        $url .= '&t[]=sepia:threshold=30';
        $url .= '&publicKey=foo-bar';
        $url .= '&accessToken=foo';

        $imgUrl = ImageUrl::factory($url, 'privkey', 'pubkey');
        $this->assertNotContains('&publicKey=pub-key', (string) $imgUrl);
    }

    public function testDoesNotIncludeAccessTokenIfPrivateKeyNotSet() {
        $url  = 'http://imbo/users/foo-bar/images/imgIdentifier.jpg';
        $url .= '?t[]=flipHorizontally';
        $url .= '&t[]=sepia:threshold=30';
        $url .= '&accessToken=foo';

        $imgUrl = ImageUrl::factory($url);
        $imgUrl->desaturate();

        $this->assertNotContains('&accessToken=', (string) $imgUrl);
    }

    public function testContinuesToBuildOnExistingTransformations() {
        $url  = 'http://imbo/users/foo-bar/images/imgIdentifier.jpg';
        $url .= '?t[]=flipHorizontally';
        $url .= '&t[]=sepia:threshold=30';

        $imgUrl = ImageUrl::factory($url);
        $imgUrl->desaturate()->autoRotate();

        $this->assertContains(
            '?t%5B0%5D=flipHorizontally&t%5B1%5D=sepia%3Athreshold%3D30&t%5B2%5D=desaturate&t%5B3%5D=autoRotate',
            (string) $imgUrl
        );
    }

    public function testRetrievesAllTransformationsRegardlessOfIndex() {
        $url  = 'http://imbo/users/foo-bar/images/imgIdentifier.jpg';
        $url .= '?t[0]=flipHorizontally';
        $url .= '&t[]=sepia:threshold=30';
        $url .= '&t=eh';
        $url .= '&t[1]=strip';

        $imgUrl = ImageUrl::factory($url);
        $this->assertCount(4, $imgUrl->getTransformations());
    }

    public function testCanResetExistingUrlToRemoveTransformationsAndExtension() {
        $url  = 'http://imbo/users/bar/images/imgIdentifier.jpg';
        $url .= '?t[]=flipHorizontally';
        $url .= '&t[]=sepia:threshold=30';

        $imgUrl = ImageUrl::factory($url, 'foo', 'bar');
        $imgUrl->reset();

        $at = '23d459cc4ad6b57bac6953c6d75cb78d3d235583d3e93f651aec7691cb427755';
        $this->assertSame(
            'http://imbo/users/bar/images/imgIdentifier?accessToken=' . $at,
            (string) $imgUrl
        );
    }

    public function testDoesNotIncludeTransformationQueryParamIfNoTransformationsAdded() {
        $url  = 'http://imbo/users/bar/images/imgIdentifier?foo=bar&z=lulz';

        $imgUrl = ImageUrl::factory($url);

        $this->assertSame(
            'http://imbo/users/bar/images/imgIdentifier?foo=bar&z=lulz',
            (string) $imgUrl
        );
    }
}
