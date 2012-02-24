<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient;

/**
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class ClientTest extends \PHPUnit_Framework_TestCase {
    /**
     * Client instance
     *
     * @var ImboClient\Client
     */
    private $client;

    /**
     * Public key
     *
     * @var string
     */
    private $publicKey;

    /**
     * Private key
     *
     * @var string
     */
    private $privateKey;

    /**
     * The server url passed to the constructor
     *
     * @var string
     */
    private $serverUrl = 'http://host';

    /**
     * Image identifier used for tests
     *
     * @var string
     */
    private $imageIdentifier;

    /**
     * Pattern used in the mock matchers when url is signed
     *
     * @var string
     */
    private $signedUrlPattern = array(
        'image'    => '|^http://host/users/[a-zA-Z0-9]{3,}/images/[a-f0-9]{32}\?signature=(.*?)&timestamp=\d\d\d\d-\d\d-\d\dT\d\d%3A\d\d%3A\d\dZ$|',
        'metadata' => '|^http://host/users/[a-zA-Z0-9]{3,}/images/[a-f0-9]{32}/meta\?signature=(.*?)&timestamp=\d\d\d\d-\d\d-\d\dT\d\d%3A\d\d%3A\d\dZ$|',
    );

    /**
     * Pattern used in the mock matchers with regular urls
     *
     * @var string
     */
    private $urlPattern = array(
        'user'     => '|^http://host/users/[a-zA-Z0-9]{3,}$|',
        'image'    => '|^http://host/users/[a-zA-Z0-9]{3,}/images/[a-f0-9]{32}$|',
        'images'   => '|^http://host/users/[a-zA-Z0-9]{3,}/images(/?\?.*)?$|',
        'metadata' => '|^http://host/users/[a-zA-Z0-9]{3,}/images/[a-f0-9]{32}/meta$|',
    );

    /**
     * Set up method
     */
    public function setUp() {
        $this->publicKey = 'publicKey';
        $this->privateKey = md5(microtime());
        $this->imageIdentifier = md5(microtime());
        $this->driver = $this->getMock('ImboClient\Driver\DriverInterface');

        $this->client = new Client($this->serverUrl, $this->publicKey, $this->privateKey, $this->driver);
    }

    /**
     * Tear down method
     */
    public function tearDown() {
        $this->driver = null;
        $this->client = null;
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File does not exist: foobar
     * @covers ImboClient\Client::addImage
     * @covers ImboClient\Client::validateLocalFile
     */
    public function testAddImageWhenLocalImageDoesNotExist() {
        $this->client->addImage('foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File is of zero length:
     * @covers ImboClient\Client::addImage
     * @covers ImboClient\Client::validateLocalFile
     */
    public function testAddImageWhenImageIsEmpty() {
        $path = __DIR__ . '/_files/emptyImage.png';
        $this->client->addImage($path);
    }

    /**
     * @covers ImboClient\Client::addImage
     * @covers ImboClient\Client::validateLocalFile
     * @covers ImboClient\Client::getSignedUrl
     * @covers ImboClient\Client::generateSignature
     */
    public function testAddImage() {
        $imagePath = __DIR__ . '/_files/image.png';
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('put')->with($this->matchesRegularExpression($this->signedUrlPattern['image']), $imagePath)->will($this->returnValue($response));
        $this->assertSame($response, $this->client->addImage($imagePath));
    }

    /**
     * @covers ImboClient\Client::addImageFromString
     * @covers ImboClient\Client::getSignedUrl
     * @covers ImboClient\Client::generateSignature
     */
    public function testAddImageFromString() {
        $imageData = file_get_contents(__DIR__ . '/_files/image.png');
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('putData')->with($this->matchesRegularExpression($this->signedUrlPattern['image']), $imageData)->will($this->returnValue($response));
        $this->assertSame($response, $this->client->addImageFromString($imageData));
    }

    /**
     * @covers ImboClient\Client::deleteImage
     * @covers ImboClient\Client::getSignedUrl
     * @covers ImboClient\Client::generateSignature
     */
    public function testDeleteImage() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('delete')->with($this->matchesRegularExpression($this->signedUrlPattern['image']))->will($this->returnValue($response));
        $this->assertSame($response, $this->client->deleteImage($this->imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::editMetadata
     * @covers ImboClient\Client::getSignedUrl
     * @covers ImboClient\Client::generateSignature
     */
    public function testEditMetadata() {
        $data = array(
            'foo' => 'bar',
            'bar' => 'foo',
        );

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('post')->with($this->matchesRegularExpression($this->signedUrlPattern['metadata']), $data)->will($this->returnValue($response));
        $this->assertSame($response, $this->client->editMetadata($this->imageIdentifier, $data));
    }

    /**
     * @covers ImboClient\Client::deleteMetadata
     * @covers ImboClient\Client::getSignedUrl
     * @covers ImboClient\Client::generateSignature
     */
    public function testDeleteMetadata() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('delete')->with($this->matchesRegularExpression($this->signedUrlPattern['metadata']))->will($this->returnValue($response));
        $this->assertSame($response, $this->client->deleteMetadata($this->imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::getMetadata
     */
    public function testGetMetadata() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('get')->with($this->matchesRegularExpression($this->urlPattern['metadata']))->will($this->returnValue($response));
        $this->assertSame($response, $this->client->getMetadata($this->imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::headImage
     */
    public function testHeadImage() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('head')->with($this->matchesRegularExpression($this->urlPattern['image']))->will($this->returnValue($response));
        $this->assertSame($response, $this->client->headImage($this->imageIdentifier));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File does not exist: foobar
     * @covers ImboClient\Client::imageExists
     */
    public function testImageExistsWhenLocalImageDoesNotExist() {
        $this->client->imageExists('foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File is of zero length:
     * @covers ImboClient\Client::imageExists
     */
    public function testImageExistsWhenLocalImageIsEmpty() {
        $path = __DIR__ . '/_files/emptyImage.png';
        $this->client->imageExists($path);
    }

    /**
     * @covers ImboClient\Client::imageExists
     */
    public function testImageExistsWhenRemoteImageDoesNotExist() {
        $imagePath = __DIR__ . '/_files/image.png';
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(404));

        $this->driver->expects($this->once())->method('head')->with($this->matchesRegularExpression($this->urlPattern['image']))->will($this->returnValue($response));

        $this->assertFalse($this->client->imageExists($imagePath));
    }

    /**
     * @covers ImboClient\Client::imageExists
     */
    public function testImageExistsWhenRemoteImageExist() {
        $imagePath = __DIR__ . '/_files/image.png';
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));

        $this->driver->expects($this->once())->method('head')->with($this->matchesRegularExpression($this->urlPattern['image']))->will($this->returnValue($response));

        $this->assertTrue($this->client->imageExists($imagePath));
    }

    /**
     * @covers ImboClient\Client::getImageUrl
     * @covers ImboClient\Client::getHostForImageIdentifier
     */
    public function testGetImageUrl() {
        $identifier = md5(microtime());
        $url = $this->client->getImageUrl($identifier);
        $this->assertInstanceOf('ImboClient\ImageUrl\ImageUrl', $url);
    }

    /**
     * @covers ImboClient\Client::getImageUrl
     * @covers ImboClient\Client::getHostForImageIdentifier
     */
    public function testGetImageUrlAsString() {
        $expectedUrl = $this->serverUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier;
        $this->assertSame($expectedUrl, $this->client->getImageUrl($this->imageIdentifier, true));
    }

    /**
     * Server hostname data provider
     *
     * @return array
     */
    public function getMultiHostServers() {
        $publicKey = md5(microtime());

        $hosts = array('http://imbo0', 'http://imbo1/prefix', 'http://imbo2:81', 'http://imbo3:81/prefix', 'http://imbo4:80');

        return array(
            array($hosts, $publicKey, 'd1afdbe2950dc1e9fa134d8c91cd1a8b', 'http://imbo4'),
            array($hosts, $publicKey, '5fda26a928c9b0b90ef7b2db0031bfcf', 'http://imbo0'),
            array($hosts, $publicKey, '5d028794b32c2b127875a336b1220dab', 'http://imbo3:81/prefix'),
            array($hosts, $publicKey, 'f7dc62518f2967dacbc4c0eead5fabe5', 'http://imbo2:81'),
            array($hosts, $publicKey, '7a4cac9e82c06010293cd6d23708e147', 'http://imbo2:81'),
            array($hosts, $publicKey, '609c8d8350d3b6b294a628835b8e9b59', 'http://imbo1/prefix'),
            array($hosts, $publicKey, '1e68c888fbe0a27276141a1e6fb576f4', 'http://imbo0'),
            array($hosts, $publicKey, '67e45db3a472a90a26bda000c0818bfc', 'http://imbo3:81/prefix'),
            array($hosts, $publicKey, '3ad35117949c5a17b9df82c343b4f763', 'http://imbo3:81/prefix'),
        );
    }

    /**
     * @dataProvider getMultiHostServers()
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::parseUrls
     * @covers ImboClient\Client::getImageUrl
     * @covers ImboClient\Client::getHostForImageIdentifier
     */
    public function testImageUrlHostnames($urls, $publicKey, $imageIdentifier, $expected) {
        $expectedUrl = $expected . '/users/' . $publicKey . '/images/' . $imageIdentifier;
        $client = new Client($urls, $publicKey, $this->privateKey);
        $this->assertSame($expectedUrl, $client->getImageUrl($imageIdentifier, true));
    }

    /**
     * @covers ImboClient\Client::getMetadataUrl
     */
    public function testGetMetadataUrl() {
        $expectedUrl = $this->serverUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '/meta';
        $this->assertSame($expectedUrl, $this->client->getMetadataUrl($this->imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::getImagesUrl
     */
    public function testGetImagesUrl() {
        $expectedUrl = $this->serverUrl . '/users/' . $this->publicKey . '/images';
        $this->assertSame($expectedUrl, $this->client->getImagesUrl());
    }

    /**
     * @covers ImboClient\Client::getNumImages
     */
    public function testGetNumImages() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $response->expects($this->once())->method('getBody')->will($this->returnValue(json_encode(array('numImages' => 42))));

        $this->driver->expects($this->once())->method('get')->with($this->matchesRegularExpression($this->urlPattern['user']))->will($this->returnValue($response));

        $this->assertSame(42, $this->client->getNumImages());
    }

    /**
     * @covers ImboClient\Client::getNumImages
     */
    public function testGetNumImagesWhenServerRespondsWithAnError() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(500));

        $this->driver->expects($this->once())->method('get')->with($this->matchesRegularExpression($this->urlPattern['user']))->will($this->returnValue($response));

        $this->assertFalse($this->client->getNumImages());
    }

    /**
     * Get images data provider
     *
     * @return array
     */
    public function getImageData() {
        return array(
            array(
                json_encode(array(
                    array(
                        'size'              => 54249,
                        'publicKey'         => '20033e31f182661dafa332b423ecce5f',
                        'imageIdentifier'   => '9c966f89db794417d474a87793ea4af8',
                        'extension'         => 'jpg',
                        'mime'              => 'image/jpeg',
                        'added'             => 1328530242,
                        'updated'           => 1328530242,
                        'width'             => 480,
                        'height'            => 360,
                        'checksum'          => '9c966f89db794417d474a87793ea4af8',
                    ),
                    array(
                        'size'              => 152972,
                        'publicKey'         => '20033e31f182661dafa332b423ecce5f',
                        'imageIdentifier'   => '9adc6809eae536f98b9559cb1f1aeed3',
                        'extension'         => 'jpg',
                        'mime'              => 'image/jpeg',
                        'added'             => 1328514845,
                        'updated'           => 1328514845,
                        'width'             => 800,
                        'height'            => 600,
                        'checksum'          => '9adc6809eae536f98b9559cb1f1aeed3',
                    )
                ))
            )
        );
    }

    /**
     * @dataProvider getImageData()
     * @covers ImboClient\Client::getImages
     */
    public function testGetImages($data) {

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $response->expects($this->once())->method('getBody')->will($this->returnValue($data));

        $this->driver->expects($this->once())->method('get')->with($this->matchesRegularExpression($this->urlPattern['images']))->will($this->returnValue($response));

        $images = $this->client->getImages();

        foreach ($images as $image) {
            $this->assertInstanceOf('ImboClient\ImagesQuery\Image', $image);
        }
    }

    /**
     * @dataProvider getImageData()
     * @covers ImboClient\Client::getImages
     */
    public function testGetImagesWithQuery($data) {

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $response->expects($this->once())->method('getBody')->will($this->returnValue($data));

        $this->driver->expects($this->once())->method('get')->with($this->stringContains('query=%7B%22foo%22%3A%22bar%22%7D'))->will($this->returnValue($response));

        $query = new ImagesQuery\Query();
        $query->page(3)->metadataQuery(array('foo' => 'bar'))->num(5);

        $images = $this->client->getImages($query);

        foreach ($images as $image) {
            $this->assertInstanceOf('ImboClient\ImagesQuery\Image', $image);
        }
    }

    /**
     * @covers ImboClient\Client::getImages
     */
    public function testGetImagesWhenServerRespondsWithAnError() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(500));

        $this->driver->expects($this->once())->method('get')->with($this->matchesRegularExpression($this->urlPattern['images']))->will($this->returnValue($response));

        $this->assertFalse($this->client->getImages());
    }

    /**
     * @covers ImboClient\Client::getImageData
     * @covers ImboClient\Client::getImageDataFromUrl
     */
    public function testGetImageData() {
        $expectedData = 'someBinaryImageData';

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $response->expects($this->once())->method('getBody')->will($this->returnValue($expectedData));

        $this->driver->expects($this->once())->method('get')->with($this->matchesRegularExpression($this->urlPattern['image']))->will($this->returnValue($response));

        $imageIdentifier = md5(microtime());
        $this->assertSame($expectedData, $this->client->getImageData($imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::getImageData
     * @covers ImboClient\Client::getImageDataFromUrl
     */
    public function testGetImageDataWhenServerRespondsWithAnError() {
        $imageUrl = $this->client->getImageUrl(md5(microtime()));

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(500));

        $this->driver->expects($this->once())->method('get')->with($this->matchesRegularExpression($this->urlPattern['image']))->will($this->returnValue($response));

        $imageIdentifier = md5(microtime());
        $this->assertSame(false, $this->client->getImageData($imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::getImageDataFromUrl
     */
    public function testGetImageDataFromUrl() {
        $expectedData = 'someBinaryImageData';

        $imageUrl = $this->client->getImageUrl(md5(microtime()));

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $response->expects($this->once())->method('getBody')->will($this->returnValue($expectedData));

        $regex = '|^http://host/users/[a-zA-Z0-9]{3,}/images/[a-f0-9]{32}\?t\[\]=|';
        $this->driver->expects($this->once())->method('get')->with($this->matchesRegularExpression($regex))->will($this->returnValue($response));

        $this->assertSame($expectedData, $this->client->getImageDataFromUrl($imageUrl->flipHorizontally()));
    }

    /**
     * Server urls data provider
     *
     * @return array
     */
    public function getServerUrls() {
        $publicKey = md5(microtime());

        return array(
            array('http://imbo', $publicKey, 'http://imbo/users/' . $publicKey),
            array('http://imbo/prefix', $publicKey, 'http://imbo/prefix/users/' . $publicKey),
            array('http://imbo:81', $publicKey, 'http://imbo:81/users/' . $publicKey),
            array('http://imbo:81/prefix', $publicKey, 'http://imbo:81/prefix/users/' . $publicKey),
            array('http://imbo:80', $publicKey, 'http://imbo/users/' . $publicKey),
            array('http://imbo:80/prefix', $publicKey, 'http://imbo/prefix/users/' . $publicKey),

            array('https://imbo', $publicKey, 'https://imbo/users/' . $publicKey),
            array('https://imbo/prefix', $publicKey, 'https://imbo/prefix/users/' . $publicKey),
            array('https://imbo:444', $publicKey, 'https://imbo:444/users/' . $publicKey),
            array('https://imbo:444/prefix', $publicKey, 'https://imbo:444/prefix/users/' . $publicKey),
            array('https://imbo:443', $publicKey, 'https://imbo/users/' . $publicKey),
            array('https://imbo:443/prefix', $publicKey, 'https://imbo/prefix/users/' . $publicKey),
        );
    }

    /**
     * @dataProvider getServerUrls()
     * @covers ImboClient\Client::getUserUrl
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::parseUrls
     */
    public function testServerUrls($url, $publicKey, $expected) {
        $client = new Client($url, $publicKey, $this->privateKey);
        $this->assertSame($expected, $client->getUserUrl());
    }

    /**
     * @covers ImboClient\Client::getImageProperties
     */
    public function testGetImagePropertiesWithImageThatDoesNotExist() {
        $image = '8f552ba2a350be7ac19399365a738202';
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(404));
        $this->driver->expects($this->once())->method('head')->will($this->returnValue($response));
        $this->assertFalse($this->client->getImageProperties($image));
    }

    /**
     * @covers ImboClient\Client::getImageProperties
     */
    public function testGetImagePropertiesWithImageThatExists() {
        $image = '8f552ba2a350be7ac19399365a738202';
        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');
        $headers->expects($this->any())->method('get')->will($this->returnCallback(function ($key) {
            switch ($key) {
                case 'x-imbo-originalwidth': return 200; break;
                case 'x-imbo-originalheight': return 100; break;
                case 'x-imbo-originalfilesize': return 400; break;
            }
        }));

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $response->expects($this->once())->method('getHeaders')->will($this->returnValue($headers));

        $this->driver->expects($this->once())->method('head')->will($this->returnValue($response));
        $properties = $this->client->getImageProperties($image);
        $this->assertInternalType('array', $properties);

        $this->assertArrayHasKey('width', $properties);
        $this->assertArrayHasKey('height', $properties);
        $this->assertArrayHasKey('filesize', $properties);

        $this->assertSame(200, $properties['width']);
        $this->assertSame(100, $properties['height']);
        $this->assertSame(400, $properties['filesize']);
    }

    /**
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::generateImageIdentifier
     */
    public function testGetImageIdentifier() {
        $imagePath = __DIR__ . '/_files/image.png';
        $this->assertSame('929db9c5fc3099f7576f5655207eba47', $this->client->getImageIdentifier($imagePath));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File does not exist: foobar
     * @covers ImboClient\Client::getImageIdentifier
     */
    public function testGetImageIdentifierWhenImageDoesNotExist() {
        $this->client->getImageIdentifier('foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File is of zero length:
     * @covers ImboClient\Client::getImageIdentifier
     */
    public function testGetImageIdentifierWhenImageIsEmpty() {
        $path = __DIR__ . '/_files/emptyImage.png';
        $this->client->getImageIdentifier($path);
    }

    /**
     * @covers ImboClient\Client::getImageIdentifierFromString
     * @covers ImboClient\Client::generateImageIdentifier
     */
    public function testGetImageIdentifierFromString() {
        $imagePath = __DIR__ . '/_files/image.png';
        $image = file_get_contents($imagePath);
        $this->assertSame('929db9c5fc3099f7576f5655207eba47', $this->client->getImageIdentifierFromString($image));
    }
}
