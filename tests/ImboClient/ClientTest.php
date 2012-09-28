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

use ImboClient\Exception\ServerException,
    ReflectionClass,
    ReflectionProperty;

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
     * The server URL passed to the constructor
     *
     * @var string
     */
    private $serverUrl = 'http://host';

    /**
     * Public key
     *
     * @var string
     */
    private $publicKey = 'key';

    /**
     * Private key
     *
     * @var string
     */
    private $privateKey = '8495c97ea3a313c12c0661dc5526e769';

    /**
     * Image identifier used for tests
     *
     * @var string
     */
    private $imageIdentifier = '23d7f91b25f3013fcc75ce070c40e004';

    /**
     * Regexp pattern that matches the end of a signed URL
     *
     * This value is used in some matchers in this test case.
     *
     * @var string
     */
    private $signedUrlPattern = '/signature=.*?&timestamp=\d\d\d\d-\d\d-\d\dT\d\d%3A\d\d%3A\d\dZ$/';

    /**
     * Set up method
     *
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::setDriver
     * @covers ImboClient\Version::getVersionString
     * @covers ImboClient\Version::getVersionNumber
     */
    public function setUp() {
        $this->driver = $this->getMock('ImboClient\Driver\DriverInterface');
        $this->driver->expects($this->at(0))->method('setRequestHeaders')->with(array(
            'Accept' => 'application/json,image/*',
            'User-Agent' => 'ImboClient-php-dev',
        ));
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
     */
    public function testAddImage() {
        $imagePath = __DIR__ . '/_files/image.png';
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('put')->with($this->matchesRegularExpression($this->signedUrlPattern), $imagePath)->will($this->returnValue($response));
        $this->assertSame($response, $this->client->addImage($imagePath));
    }

    /**
     * @covers ImboClient\Client::addImageFromString
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testAddImageFromString() {
        $imageData = file_get_contents(__DIR__ . '/_files/image.png');
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('putData')->with($this->matchesRegularExpression($this->signedUrlPattern), $imageData)->will($this->returnValue($response));
        $this->assertSame($response, $this->client->addImageFromString($imageData));
    }

    /**
     * @covers ImboClient\Client::addImageFromUrl
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testAddImageFromUrlWithString() {
        $url = 'http://example.com/image.jpg';
        $data = 'binary image data';

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('isSuccess')->will($this->returnValue(true));
        $response->expects($this->once())->method('getBody')->will($this->returnValue($data));

        $this->driver->expects($this->once())->method('get')->with($url)->will($this->returnValue($response));
        $this->driver->expects($this->once())->method('putData')->with($this->matchesRegularExpression($this->signedUrlPattern), $data)->will($this->returnValue($response));
        $this->assertSame($response, $this->client->addImageFromUrl($url));
    }

    /**
     * @covers ImboClient\Client::addImageFromUrl
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testAddImageFromUrlWithInstanceOfImageUrl() {
        $url = 'http://example.com/image.jpg?accessToken=token';
        $data = 'binary image data';

        $imageUrl = $this->getMock('ImboClient\Url\ImageInterface');
        $imageUrl->expects($this->once())->method('getUrl')->will($this->returnValue($url));

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('isSuccess')->will($this->returnValue(true));
        $response->expects($this->once())->method('getBody')->will($this->returnValue($data));

        $this->driver->expects($this->once())->method('get')->with($url)->will($this->returnValue($response));
        $this->driver->expects($this->once())->method('putData')->with($this->matchesRegularExpression($this->signedUrlPattern), $data)->will($this->returnValue($response));

        $this->assertSame($response, $this->client->addImageFromUrl($imageUrl));
    }

    /**
     * @covers ImboClient\Client::addImageFromUrl
     */
    public function testAddImageFromUrlWithInvalidUrl() {
        $url = 'http://example.com/image.jpg';

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('isSuccess')->will($this->returnValue(false));

        $this->driver->expects($this->once())->method('get')->with($url)->will($this->returnValue($response));
        $this->assertSame($response, $this->client->addImageFromUrl($url));
    }

    /**
     * @covers ImboClient\Client::deleteImage
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testDeleteImage() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('delete')->with($this->matchesRegularExpression($this->signedUrlPattern))->will($this->returnValue($response));
        $this->assertSame($response, $this->client->deleteImage($this->imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::editMetadata
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testEditMetadata() {
        $data = array(
            'foo' => 'bar',
            'bar' => 'foo',
        );
        $encodedData = json_encode($data);

        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('post')->with($this->matchesRegularExpression($this->signedUrlPattern), $encodedData)->will($this->returnValue($response));
        $this->assertSame($response, $this->client->editMetadata($this->imageIdentifier, $data));
    }

    /**
     * @covers ImboClient\Client::replaceMetadata
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testReplaceMetadata() {
        $metadata = array('foo' => 'bar');
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('putData')->with($this->matchesRegularExpression($this->signedUrlPattern), '{"foo":"bar"}')->will($this->returnValue($response));
        $this->assertSame($response, $this->client->replaceMetadata($this->imageIdentifier, $metadata));
    }

    /**
     * @covers ImboClient\Client::deleteMetadata
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testDeleteMetadata() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('delete')->with($this->matchesRegularExpression($this->signedUrlPattern))->will($this->returnValue($response));
        $this->assertSame($response, $this->client->deleteMetadata($this->imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::getMetadata
     */
    public function testGetMetadata() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue($response));
        $this->assertSame($response, $this->client->getMetadata($this->imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::headImage
     */
    public function testHeadImage() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $this->driver->expects($this->once())->method('head')->with($this->isType('string'))->will($this->returnValue($response));
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

        $this->driver->expects($this->once())->method('head')->with($this->isType('string'))->will($this->returnValue($response));

        $this->assertFalse($this->client->imageExists($imagePath));
    }

    /**
     * @covers ImboClient\Client::imageExists
     */
    public function testImageExistsWhenRemoteImageExist() {
        $imagePath = __DIR__ . '/_files/image.png';
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));

        $this->driver->expects($this->once())->method('head')->with($this->isType('string'))->will($this->returnValue($response));

        $this->assertTrue($this->client->imageExists($imagePath));
    }

    /**
     * @covers ImboClient\Client::getImageUrl
     * @covers ImboClient\Client::getHostForImageIdentifier
     */
    public function testGetImageUrl() {
        $identifier = md5(microtime());
        $url = $this->client->getImageUrl($identifier);
        $this->assertInstanceOf('ImboClient\Url\ImageInterface', $url);
    }

    /**
     * Server hostname data provider
     *
     * @return array
     */
    public function getMultiHostServers() {
        $hosts = array(
            'http://imbo0',
            'http://imbo1/prefix',
            'http://imbo2:81',
            'http://imbo3:81/prefix',
            'http://imbo4:80',
        );

        return array(
            array($hosts, 'd1afdbe2950dc1e9fa134d8c91cd1a8b', 'http://imbo4'),
            array($hosts, '5fda26a928c9b0b90ef7b2db0031bfcf', 'http://imbo0'),
            array($hosts, '5d028794b32c2b127875a336b1220dab', 'http://imbo3:81/prefix'),
            array($hosts, 'f7dc62518f2967dacbc4c0eead5fabe5', 'http://imbo2:81'),
            array($hosts, '7a4cac9e82c06010293cd6d23708e147', 'http://imbo2:81'),
            array($hosts, '609c8d8350d3b6b294a628835b8e9b59', 'http://imbo1/prefix'),
            array($hosts, '1e68c888fbe0a27276141a1e6fb576f4', 'http://imbo0'),
            array($hosts, '67e45db3a472a90a26bda000c0818bfc', 'http://imbo3:81/prefix'),
            array($hosts, '3ad35117949c5a17b9df82c343b4f763', 'http://imbo3:81/prefix'),
        );
    }

    /**
     * @dataProvider getMultiHostServers()
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::parseUrls
     * @covers ImboClient\Client::getHostForImageIdentifier
     */
    public function testImageUrlHostnames($urls, $imageIdentifier, $expected) {
        $client = new Client($urls, $this->publicKey, $this->privateKey, $this->getMock('ImboClient\Driver\DriverInterface'));

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('getHostForImageIdentifier');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($client, $imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::getMetadataUrl
     */
    public function testGetMetadataUrl() {
        $this->assertInstanceOf('ImboClient\Url\Metadata', $this->client->getMetadataUrl($this->imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::getUserUrl
     */
    public function testGetUserUrl() {
        $this->assertInstanceOf('ImboClient\Url\User', $this->client->getUserUrl());
    }

    /**
     * @covers ImboClient\Client::getStatusUrl
     */
    public function testGetStatusUrl() {
        $this->assertInstanceOf('ImboClient\Url\Status', $this->client->getStatusUrl());
    }

    /**
     * @covers ImboClient\Client::getImagesUrl
     */
    public function testGetImagesUrl() {
        $this->assertInstanceOf('ImboClient\Url\Images', $this->client->getImagesUrl());
    }

    /**
     * @covers ImboClient\Client::getNumImages
     */
    public function testGetNumImages() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $response->expects($this->once())->method('getBody')->will($this->returnValue(json_encode(array('numImages' => 42))));

        $this->driver->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue($response));

        $this->assertSame(42, $this->client->getNumImages());
    }

    /**
     * @covers ImboClient\Client::getNumImages
     */
    public function testGetNumImagesWhenServerRespondsWithAnError() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(500));

        $this->driver->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue($response));

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

        $this->driver->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue($response));

        $images = $this->client->getImages();

        foreach ($images as $image) {
            $this->assertInstanceOf('ImboClient\Url\Images\Image', $image);
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

        $this->driver->expects($this->once())->method('get')->with($this->stringContains('page=3&limit=5&query=' . urlencode('{"foo":"bar"}')))->will($this->returnValue($response));

        $query = $this->getMock('ImboClient\Url\Images\QueryInterface');
        $query->expects($this->once())->method('page')->will($this->returnValue(3));
        $query->expects($this->once())->method('metadataQuery')->will($this->returnValue(array('foo' => 'bar')));
        $query->expects($this->once())->method('limit')->will($this->returnValue(5));

        $images = $this->client->getImages($query);

        foreach ($images as $image) {
            $this->assertInstanceOf('ImboClient\Url\Images\Image', $image);
        }
    }

    /**
     * @covers ImboClient\Client::getImages
     */
    public function testGetImagesWhenServerRespondsWithAnError() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(500));

        $this->driver->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue($response));

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

        $this->driver->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue($response));

        $this->assertSame($expectedData, $this->client->getImageData($this->imageIdentifier));
    }

    /**
     * @covers ImboClient\Client::getImageData
     * @covers ImboClient\Client::getImageDataFromUrl
     */
    public function testGetImageDataWhenServerRespondsWithAnError() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getStatusCode')->will($this->returnValue(500));

        $this->driver->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue($response));

        $this->assertSame(false, $this->client->getImageData($this->imageIdentifier));
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
     * Server URLs data provider
     *
     * @return array
     */
    public function getServerUrls() {
        $publicKey = md5(microtime());

        return array(
            array('http://imbo', 'http://imbo'),
            array('http://imbo/prefix', 'http://imbo/prefix'),
            array('http://imbo:81', 'http://imbo:81'),
            array('http://imbo:81/prefix', 'http://imbo:81/prefix'),
            array('http://imbo:80', 'http://imbo'),
            array('http://imbo:80/prefix', 'http://imbo/prefix'),

            array('https://imbo', 'https://imbo'),
            array('https://imbo/prefix', 'https://imbo/prefix'),
            array('https://imbo:444', 'https://imbo:444'),
            array('https://imbo:444/prefix', 'https://imbo:444/prefix'),
            array('https://imbo:443', 'https://imbo'),
            array('https://imbo:443/prefix', 'https://imbo/prefix'),
        );
    }

    /**
     * @dataProvider getServerUrls()
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::getServerUrls
     * @covers ImboClient\Client::parseUrls
     */
    public function testServerUrls($url, $expected) {
        $client = new Client($url, 'publicKey', 'privateKey', $this->getMock('ImboClient\Driver\DriverInterface'));
        $urls = $client->getServerUrls();

        $this->assertInternalType('array', $urls);
        $this->assertCount(1, $urls);
        $this->assertSame($expected, $urls[0]);
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

    public function getSignatureData() {
        return array(
            array(
                'PUT', 'http://imbo/users/' . $this->publicKey . '/images/' . $this->imageIdentifier, '2012-03-14T10:04:06Z',
                '2237c6da85b7270e443ce07e2788e0df858abba3e83059b984d2822c36d2b4ba'
            ),
            array(
                'PUT', 'http://imbo/users/' . $this->publicKey . '/images/' . $this->imageIdentifier, '2012-03-14T10:04:07Z',
                '6b0db609609a6dec864a50ad41bdd1077df04f8f1bf40104cde249ad913c6ec3'
            ),
            array(
                'POST', 'http://imbo/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '/meta', '2012-03-14T10:04:06Z',
                '29efe947d53fa0e51d416a5a05f01420ec27c8f182c44b63186574b5d54f8a8c'
            ),
            array(
                'POST', 'http://imbo/users/' . $this->publicKey . '/images/' . $this->imageIdentifier . '/meta', '2012-03-14T10:04:07Z',
                'c6cd382a60b4bd988a2d8426000af353e7a5569a42604e5a30a7a65bce77d334'
            ),
            array(
                'DELETE', 'http://imbo/users/' . $this->publicKey . '/images/' . $this->imageIdentifier, '2012-03-14T10:04:06Z',
                '48d605563d761d2155939873fe4a820fbcac69c1d3e3df82c3ac92097f2ae9f2'
            ),
            array(
                'DELETE', 'http://imbo/users/' . $this->publicKey . '/images/' . $this->imageIdentifier, '2012-03-14T10:04:07Z',
                '242f79f025ce7c463b8826e87c335293c2bf552bea11ea6b47c5ed5ae6657061'
            ),
        );
    }

    /**
     * @dataProvider getSignatureData
     * @covers ImboClient\Client::generateSignature
     */
    public function testGenerateSignature($httpMethod, $url, $timestamp, $expected) {
        $client = new Client($this->serverUrl, $this->publicKey, $this->privateKey, $this->getMock('ImboClient\Driver\DriverInterface'));

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('generateSignature');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($client, $httpMethod, $url, $timestamp));
    }

    /**
     * @covers ImboClient\Client::getServerStatus
     */
    public function testGetServerStatusWhenServerResponseWithUnsupportedData() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getBody')->will($this->returnValue('some string'));
        $this->driver->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue($response));

        $this->assertFalse($this->client->getServerStatus());
    }

    /**
     * @covers ImboClient\Client::getServerStatus
     */
    public function testGetServerStatus() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');
        $response->expects($this->once())->method('getBody')->will($this->returnValue('{"date":"some date","database":true,"storage":false}'));
        $this->driver->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue($response));

        $status = $this->client->getServerStatus();

        $this->assertInternalType('array', $status);
    }

    public function getUrls() {
        return array(
            array('imbo', array('http://imbo')),
            array('http://imbo', array('http://imbo')),
            array(array('imbo', 'http://imbo', 'https://imbo', 'imbo2'), array('http://imbo', 'https://imbo', 'http://imbo2')),
        );
    }

    /**
     * @dataProvider getUrls()
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::parseUrls
     */
    public function testParseUrlsShouldAddMissingHttp($url, $expected) {
        $client = new Client($url, $this->publicKey, $this->privateKey, $this->getMock('ImboClient\Driver\DriverInterface'));

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('parseUrls');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($client, $url));
    }

    /**
     * @covers ImboClient\Client::imageExists
     */
    public function testImageExistsMustReturnFalseWhenDriverThrowsExceptionWith404() {
        $imagePath = __DIR__ . '/_files/image.png';
        $this->driver->expects($this->once())->method('head')->will($this->throwException(new ServerException('Message', 404)));
        $this->assertFalse($this->client->imageExists($imagePath));
    }

    /**
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage Message
     * @expectedExceptionCode 503
     * @covers ImboClient\Client::imageExists
     */
    public function testImageExistsMustReThrowExceptionWhenNot404() {
        $imagePath = __DIR__ . '/_files/image.png';
        $this->driver->expects($this->once())->method('head')->will($this->throwException(new ServerException('Message', 503)));
        $this->client->imageExists($imagePath);
    }

    /**
     * @covers ImboClient\Client::__construct
     */
    public function testClientShouldUseTheDefaultDriverIfOneIsNotSpecifiedInTheConstructor() {
        $client = new Client('http://host', 'publicKey', 'privateKey');
        $property = new ReflectionProperty('ImboClient\Client', 'driver');
        $property->setAccessible(true);
        $this->assertInstanceOf('ImboClient\Driver\cURL', $property->getValue($client));
    }
}
