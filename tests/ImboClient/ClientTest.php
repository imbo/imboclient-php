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
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient;

use ImboClient\Exception\ServerException,
    ImboClient\Exception\RuntimeException,
    ReflectionMethod,
    ReflectionProperty;

/**
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class ClientTest extends \PHPUnit_Framework_TestCase {
    /**
     * Client instance
     *
     * @var Client
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
     * Set up the client and the driver mock
     *
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::setDriver
     * @covers ImboClient\Version::getVersionString
     * @covers ImboClient\Version::getVersionNumber
     */
    public function setUp() {
        $this->driver = $this->getMock('ImboClient\Driver\DriverInterface');
        $this->driver->expects($this->at(0))->method('setRequestHeaders')->with($this->isType('array'));
        $this->client = new Client($this->serverUrl, $this->publicKey, $this->privateKey, $this->driver);
    }

    /**
     * Tear down the client and the driver mock
     */
    public function tearDown() {
        $this->driver = null;
        $this->client = null;
    }

    /**
     * When trying to add a local image that does not exist the client must throw an exception
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File does not exist: foobar
     * @covers ImboClient\Client::addImage
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::validateLocalFile
     */
    public function testThrowsExceptionWhenTryingToAddLocalImageThatDoesNotExist() {
        $this->client->addImage('foobar');
    }

    /**
     * When trying to add an empty local image the client must throw an exception
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File is of zero length:
     * @covers ImboClient\Client::addImage
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::validateLocalFile
     */
    public function testThrowsExceptionWhenTryingToAddEmptyLocalImage() {
        $path = __DIR__ . '/_files/emptyImage.png';
        $this->client->addImage($path);
    }

    /**
     * The client must be able to add a valid local image
     *
     * @covers ImboClient\Client::addImage
     * @covers ImboClient\Client::getImageUrl
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::validateLocalFile
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testReturnsResponseWhenAddingValidLocalImage() {
        $imagePath = __DIR__ . '/_files/image.png';
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('put')
                     ->with($this->matchesRegularExpression($this->signedUrlPattern), $imagePath)
                     ->will($this->returnValue($response));

        $this->assertSame($response, $this->client->addImage($imagePath));
    }

    /**
     * The client must be able to add an in-memory image
     *
     * @covers ImboClient\Client::addImageFromString
     * @covers ImboClient\Client::getImageIdentifierFromString
     * @covers ImboClient\Client::getImageUrl
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testReturnsResponseWhenAddingInMemoryImage() {
        $imageData = file_get_contents(__DIR__ . '/_files/image.png');
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('putData')
                     ->with($this->matchesRegularExpression($this->signedUrlPattern), $imageData)
                     ->will($this->returnValue($response));

        $this->assertSame($response, $this->client->addImageFromString($imageData));
    }

    /**
     * The client must throw an exception when trying to add an empty in-memory image
     *
     * @expectedException ImboClient\Exception\InvalidArgumentException
     * @expectedExceptionMessage Specified image is empty
     * @covers ImboClient\Client::addImageFromString
     */
    public function testThrowsExceptionWhenTryingToAddEmptyInMemoryImage() {
        $this->client->addImageFromString('');
    }

    /**
     * The client must be able to fetch an image from an URL and add it
     *
     * @covers ImboClient\Client::addImageFromUrl
     * @covers ImboClient\Client::addImageFromString
     */
    public function testReturnsResponseWhenAddingARemoteImage() {
        $url = 'http://example.com/image.jpg';
        $data = 'binary image data';

        $response = $this->getResponseMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue($data));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($url)
                     ->will($this->returnValue($response));

        $this->driver->expects($this->once())
                     ->method('putData')
                     ->with($this->matchesRegularExpression($this->signedUrlPattern), $data)
                     ->will($this->returnValue($response));

        $this->assertSame($response, $this->client->addImageFromUrl($url));
    }

    /**
     * The client must throw an exception when trying to add an empty remote image
     *
     * @expectedException ImboClient\Exception\InvalidArgumentException
     * @expectedExceptionMessage Specified image is empty
     * @covers ImboClient\Client::addImageFromUrl
     * @covers ImboClient\Client::addImageFromString
     */
    public function testThrowsExceptionWhenTryingToAddEmptyRemoteImage() {
        $url = 'http://example.com/image.jpg';
        $data = '';

        $response = $this->getResponseMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue($data));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($url)
                     ->will($this->returnValue($response));

        $this->client->addImageFromUrl($url);
    }

    /**
     * The client must be able to fetch an image from an URL specified by an ImboClient\Url\Image
     * instance and add it
     *
     * @covers ImboClient\Client::addImageFromUrl
     * @covers ImboClient\Client::addImageFromString
     */
    public function testReturnsResponseWhenAddingARemoteImageSpecifiedByImageUrlInstance() {
        $url = 'http://example.com/image.jpg?accessToken=token';
        $data = 'binary image data';

        $imageUrl = $this->getMock('ImboClient\Url\ImageInterface');
        $imageUrl->expects($this->once())->method('getUrl')->will($this->returnValue($url));

        $response = $this->getResponseMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue($data));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($url)
                     ->will($this->returnValue($response));

        $this->driver->expects($this->once())
                     ->method('putData')
                     ->with($this->matchesRegularExpression($this->signedUrlPattern), $data)
                     ->will($this->returnValue($response));

        $this->assertSame($response, $this->client->addImageFromUrl($imageUrl));
    }

    /**
     * The client must return a valid response after deleting a remote image
     *
     * @covers ImboClient\Client::deleteImage
     * @covers ImboClient\Client::getImageUrl
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testReturnsResponseAfterDeletingAnImage() {
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('delete')
                     ->with($this->matchesRegularExpression($this->signedUrlPattern))
                     ->will($this->returnValue($response));

        $this->assertSame($response, $this->client->deleteImage($this->imageIdentifier));
    }

    /**
     * The client must return a valid response after editing metadata
     *
     * @covers ImboClient\Client::editMetadata
     * @covers ImboClient\Client::getMetadataUrl
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testReturnsResponseAfterEditingMetadata() {
        $metadata = array(
            'foo' => 'bar',
            'bar' => 'foo',
        );
        $encodedMetadata = json_encode($metadata);
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('post')
                     ->with(
                         $this->matchesRegularExpression($this->signedUrlPattern),
                         $encodedMetadata,
                         $this->isType('array')
                     )
                     ->will($this->returnValue($response));

        $this->assertSame($response, $this->client->editMetadata($this->imageIdentifier, $metadata));
    }

    /**
     * The client must return a valid response after having replaced metadata
     *
     * @covers ImboClient\Client::replaceMetadata
     * @covers ImboClient\Client::getMetadataUrl
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testReturnsResponseAfterReplacingMetadata() {
        $metadata = array('foo' => 'bar');
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('putData')
                     ->with(
                         $this->matchesRegularExpression($this->signedUrlPattern),
                         '{"foo":"bar"}'
                     )
                     ->will($this->returnValue($response));

        $this->assertSame(
            $response,
            $this->client->replaceMetadata($this->imageIdentifier, $metadata)
        );
    }

    /**
     * The client must return a valid response after having deleted metadata
     *
     * @covers ImboClient\Client::deleteMetadata
     * @covers ImboClient\Client::getMetadataUrl
     * @covers ImboClient\Client::getSignedUrl
     */
    public function testReturnsResponseAfterDeletingMetadata() {
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('delete')
                     ->with($this->matchesRegularExpression($this->signedUrlPattern))
                     ->will($this->returnValue($response));

        $this->assertSame($response, $this->client->deleteMetadata($this->imageIdentifier));
    }

    /**
     * The client must return metadata as an array when fetching metadata
     *
     * @covers ImboClient\Client::getMetadata
     * @covers ImboClient\Client::getMetadataUrl
     */
    public function testReturnsArrayWithMetadataWhenFetchingMetadata() {
        $responseBody = '{"foo":"bar"}';

        $response = $this->getResponseMock();
        $response->expects($this->once())
                 ->method('getBody')
                 ->will($this->returnValue($responseBody));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($this->isType('string'))
                     ->will($this->returnValue($response));

        $this->assertSame(
            array('foo' => 'bar'),
            $this->client->getMetadata($this->imageIdentifier)
        );
    }

    /**
     * The client must return a valid response after requesting an image using HEAD
     *
     * @covers ImboClient\Client::headImage
     * @covers ImboClient\Client::getImageUrl
     */
    public function testReturnsResponseAfterRequestingImageUsingHttpHead() {
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('head')
                     ->with($this->isType('string'))
                     ->will($this->returnValue($response));

        $this->assertSame($response, $this->client->headImage($this->imageIdentifier));
    }

    /**
     * The client must throw an exception when checking if an image exists using a local image that
     * does not exist
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File does not exist: foobar
     * @covers ImboClient\Client::imageExists
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::validateLocalFile
     */
    public function testThrowsExceptionWhenCheckingIfAnImageExistsUsingALocalImageThatDoesNotExist() {
        $this->client->imageExists('foobar');
    }

    /**
     * The client must throw an exception when checking if an image exists using an empty local
     * image
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File is of zero length:
     * @covers ImboClient\Client::imageExists
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::validateLocalFile
     */
    public function testThrowsExceptionWhenCheckingIfAnImageExistsUsingAnEmptyLocalImage() {
        $path = __DIR__ . '/_files/emptyImage.png';
        $this->client->imageExists($path);
    }

    /**
     * When checking if a remote image does not exist using a valid local file the client must
     * return false
     *
     * @covers ImboClient\Client::imageExists
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::headImage
     */
    public function testReturnsFalseWhenCheckingIfARemoteImageExistsAndTheImageDoesNotExist() {
        $imagePath = __DIR__ . '/_files/image.png';
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('head')
                     ->with($this->isType('string'))
                     ->will($this->throwException(
                         new ServerException('Image does not exist', 404)
                     ));

        $this->assertFalse($this->client->imageExists($imagePath));
    }

    /**
     * The client must return true when the remote image exists
     *
     * @covers ImboClient\Client::imageExists
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::headImage
     */
    public function testReturnsTrueWhenCheckingIfARemoteImageExistsAndTheImageExists() {
        $imagePath = __DIR__ . '/_files/image.png';
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('head')
                     ->with($this->isType('string'))
                     ->will($this->returnValue($response));

        $this->assertTrue($this->client->imageExists($imagePath));
    }

    /**
     * If the server responds with an error other than 404 the client must re-throw the exception
     *
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage Internal Server Error
     * @expectedExceptionCode 500
     * @covers ImboClient\Client::imageExists
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::headImage
     */
    public function testThrowsExceptionWhenCheckingIfAnImageExistsAndTheServerRespondsWithAnErrorOtherThan404() {
        $imagePath = __DIR__ . '/_files/image.png';
        $this->driver->expects($this->once())
                     ->method('head')
                     ->will($this->throwException(
                         new ServerException('Internal Server Error', 500)
                     ));

        $this->client->imageExists($imagePath);
    }

    /**
     * When checking if an image identifier exists on the server and it does not,
     * the client must return false
     *
     * @covers ImboClient\Client::imageIdentifierExists
     * @covers ImboClient\Client::headImage
     */
    public function testReturnsFalseWhenCheckingIfARemoteImageIdentifierExistsAndTheImageDoesNotExist() {
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('head')
                     ->with($this->isType('string'))
                     ->will($this->throwException(
                         new ServerException('Image does not exist', 404)
                     ));

        $this->assertFalse($this->client->imageIdentifierExists($this->imageIdentifier));
    }

    /**
     * When checking if an image identifier exists on the server and it does,
     * the client must return true
     *
     * @covers ImboClient\Client::imageIdentifierExists
     * @covers ImboClient\Client::headImage
     */
    public function testReturnsTrueWhenCheckingIfARemoteImageIdentifierExistsAndTheImageExists() {
        $response = $this->getResponseMock();

        $this->driver->expects($this->once())
                     ->method('head')
                     ->with($this->isType('string'))
                     ->will($this->returnValue($response));

        $this->assertTrue($this->client->imageIdentifierExists($this->imageIdentifier));
    }

    /**
     * If the server responds with an error other than 404 the client must re-throw the exception
     *
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage Internal Server Error
     * @expectedExceptionCode 500
     * @covers ImboClient\Client::imageIdentifierExists
     * @covers ImboClient\Client::headImage
     */
    public function testThrowsExceptionWhenCheckingIfAnImageIdentifierExistsAndTheServerRespondsWithAnErrorOtherThan404() {
        $this->driver->expects($this->once())
                     ->method('head')
                     ->will($this->throwException(
                         new ServerException('Internal Server Error', 500)
                     ));

        $this->client->imageIdentifierExists($this->imageIdentifier);
    }

    /**
     * The client must be able to return an ImboClient\Url\Image instance based on an image
     * identifier
     *
     * @covers ImboClient\Client::getImageUrl
     * @covers ImboClient\Client::getHostForImageIdentifier
     */
    public function testCanGenerateAnImageUrlInstanceBasedOnAnImageIdentifier() {
        $this->assertInstanceOf(
            'ImboClient\Url\ImageInterface',
            $this->client->getImageUrl($this->imageIdentifier)
        );
    }

    /**
     * The client must be able to return an ImboClient\Url\Metadata instance based on an image
     * identifier
     *
     * @covers ImboClient\Client::getMetadataUrl
     * @covers ImboClient\Client::getHostForImageIdentifier
     */
    public function testCanGenerateAMetadataUrlInstanceBasedOnAnImageIdentifier() {
        $this->assertInstanceOf(
            'ImboClient\Url\Metadata',
            $this->client->getMetadataUrl($this->imageIdentifier)
        );
    }

    /**
     * The client must be able to return an ImboClient\Url\User instance based on the info given in
     * the constructor
     *
     * @covers ImboClient\Client::getUserUrl
     */
    public function testCanGenerateAnUserUrlInstanceBasedOnParametersToConstructor() {
        $this->assertInstanceOf('ImboClient\Url\User', $this->client->getUserUrl());
    }

    /**
     * The client must be able to return an ImboClient\Url\Status instance based on the info given
     * in the constructor
     *
     * @covers ImboClient\Client::getStatusUrl
     */
    public function testCanGenerateAStatusUrlInstanceBasedOnParametersToConstructor() {
        $this->assertInstanceOf('ImboClient\Url\Status', $this->client->getStatusUrl());
    }

    /**
     * The client must be able to return an ImboClient\Url\Images instance based on the info given
     * in the constructor
     *
     * @covers ImboClient\Client::getImagesUrl
     */
    public function testCanGenerateAnImagesUrlInstanceBasedOnParametersToConstructor() {
        $this->assertInstanceOf('ImboClient\Url\Images', $this->client->getImagesUrl());
    }

    /**
     * The client always selects the first URL in the set when generating an ImboClient\Url\Images
     * instance
     *
     * @covers ImboClient\Client::getStatusUrl
     */
    public function testSelectsTheFirstUrlInTheSetWhenGeneratingAStatusUrlInstance() {
        $hosts = array(
            'http://imbo1',
            'http://imbo2',
            'http://imbo3',
        );
        $client = new Client($hosts, $this->publicKey, $this->privateKey);
        $url = $client->getStatusUrl();
        $this->assertStringStartsWith('http://imbo1', $url->getUrl());

    }

    /**
     * The client always selects the first URL in the set when generating an ImboClient\Url\Images
     * instance
     *
     * @covers ImboClient\Client::getUserUrl
     */
    public function testSelectsTheFirstUrlInTheSetWhenGeneratingAnUserUrlInstance() {
        $hosts = array(
            'http://imbo1',
            'http://imbo2',
            'http://imbo3',
        );
        $client = new Client($hosts, $this->publicKey, $this->privateKey);
        $url = $client->getUserUrl();
        $this->assertStringStartsWith('http://imbo1', $url->getUrl());
    }

    /**
     * The client always selects the first URL in the set when generating an ImboClient\Url\Images
     * instance
     *
     * @covers ImboClient\Client::getImagesUrl
     */
    public function testSelectsTheFirstUrlInTheSetWhenGeneratingAnImagesUrlInstance() {
        $hosts = array(
            'http://imbo1',
            'http://imbo2',
            'http://imbo3',
        );
        $client = new Client($hosts, $this->publicKey, $this->privateKey);
        $url = $client->getImagesUrl();
        $this->assertStringStartsWith('http://imbo1', $url->getUrl());
    }

    /**
     * Server hostname data provider
     *
     * @return array[]
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
     * The client must be able to pick the same host from a set of hosts based on an image
     * identifier every time
     *
     * @dataProvider getMultiHostServers()
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::parseUrls
     * @covers ImboClient\Client::getHostForImageIdentifier
     */
    public function testSelectsTheSameHostFromASetOfHostsBasedOnAnImageIdentifierEveryTime($hosts, $imageIdentifier, $expected) {
        $client = new Client($hosts, $this->publicKey, $this->privateKey);

        $method = new ReflectionMethod('ImboClient\Client', 'getHostForImageIdentifier');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($client, $imageIdentifier));
    }

    /**
     * The client must be able to return the number of images a user has stored remotely
     *
     * @covers ImboClient\Client::getNumImages
     * @covers ImboClient\Client::getUserUrl
     */
    public function testCanFetchNumberOfImagesAUserHas() {
        $response = $this->getResponseMock();
        $response->expects($this->once())
                 ->method('getBody')
                 ->will($this->returnValue(json_encode(array('numImages' => 42))));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($this->isType('string'))
                     ->will($this->returnValue($response));

        $this->assertSame(42, $this->client->getNumImages());
    }

    /**
     * Get images data provider
     *
     * This data provider returns data in the format the images resource on the imbo server does
     *
     * @return array[]
     */
    public function getImageData() {
        return array(
            array(
                json_encode(array(
                    array(
                        'size' => 45826,
                        'publicKey' => 'christer',
                        'imageIdentifier' => '52116c74f6fba61bbc30c225d292d647',
                        'extension' => 'png',
                        'mime' => 'image/png',
                        'added' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'updated' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'width' => 380,
                        'height' => 390,
                        'checksum' => '52116c74f6fba61bbc30c225d292d647',
                    ),
                    array(
                        'size' => 10904,
                        'publicKey' => 'christer',
                        'imageIdentifier' => '3aae75c6085a7099114af8018e24c1cc',
                        'extension' => 'png',
                        'mime' => 'image/png',
                        'added' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'updated' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'width' => 210,
                        'height' => 208,
                        'checksum' => '3aae75c6085a7099114af8018e24c1cc',
                    ),
                    array(
                        'size' => 51424,
                        'publicKey' => 'christer',
                        'imageIdentifier' => '00f1e89ddfab8adb3ec018248bb96f5b',
                        'extension' => 'jpg',
                        'mime' => 'image/jpeg',
                        'added' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'updated' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'width' => 334,
                        'height' => 500,
                        'checksum' => '00f1e89ddfab8adb3ec018248bb96f5b',
                    ),
                    array(
                        'size' => 98457,
                        'publicKey' => 'christer',
                        'imageIdentifier' => '1ac50f402c1bf884483d8e42166edbbd',
                        'extension' => 'jpg',
                        'mime' => 'image/jpeg',
                        'added' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'updated' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'width' => 640,
                        'height' => 480,
                        'checksum' => '1ac50f402c1bf884483d8e42166edbbd',
                    ),
                    array(
                        'size' => 99899,
                        'publicKey' => 'christer',
                        'imageIdentifier' => '13e9bdd2b8f6b95d53ba5f4b66ecf2dc',
                        'extension' => 'jpg',
                        'mime' => 'image/jpeg',
                        'added' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'updated' => 'Thu, 27 Sep 2012 10:12:34 GMT',
                        'width' => 640,
                        'height' => 480,
                        'checksum' => '13e9bdd2b8f6b95d53ba5f4b66ecf2dc',
                    ),
                ))
            )
        );
    }

    /**
     * The client must be able to fetch images using no particular query
     *
     * @dataProvider getImageData()
     * @covers ImboClient\Client::getImages
     * @covers ImboClient\Client::getImagesUrl
     */
    public function testCanFetchImagesWithoutUsingAQueryObject($data) {
        $response = $this->getResponseMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue($data));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($this->isType('string'))
                     ->will($this->returnValue($response));

        $images = $this->client->getImages();

        foreach ($images as $image) {
            $this->assertInstanceOf('ImboClient\Url\Images\Image', $image);
        }
    }

    /**
     * The client must be able to fetch images using a query object
     *
     * @dataProvider getImageData()
     * @covers ImboClient\Client::getImages
     * @covers ImboClient\Client::getImagesUrl
     */
    public function testCanFetchImagesUsingAQueryObject($data) {
        $response = $this->getResponseMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue($data));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($this->stringContains('page=3&limit=5&query=' . urlencode('{"foo":"bar"}')))
                     ->will($this->returnValue($response));

        $query = $this->getMock('ImboClient\Url\Images\QueryInterface');
        $query->expects($this->once())->method('page')->will($this->returnValue(3));
        $query->expects($this->once())->method('limit')->will($this->returnValue(5));
        $query->expects($this->once())->method('returnMetadata')->will($this->returnValue(false));
        $query->expects($this->once())->method('from')->will($this->returnValue(null));
        $query->expects($this->once())->method('to')->will($this->returnValue(null));
        $query->expects($this->once())->method('metadataQuery')->will($this->returnValue(array('foo' => 'bar')));

        $images = $this->client->getImages($query);

        foreach ($images as $image) {
            $this->assertInstanceOf('ImboClient\Url\Images\Image', $image);
        }
    }

    /**
     * The client must be able to return the binary image data from a remote image using an image identifier
     *
     * @covers ImboClient\Client::getImageData
     * @covers ImboClient\Client::getImageUrl
     * @covers ImboClient\Client::getImageDataFromUrl
     */
    public function testCanFetchBinaryImageDataBasedOnAnImageIdentifier() {
        $expectedData = 'someBinaryImageData';

        $response = $this->getResponseMock();
        $response->expects($this->once())
                 ->method('getBody')
                 ->will($this->returnValue($expectedData));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($this->isType('string'))
                     ->will($this->returnValue($response));

        $this->assertSame($expectedData, $this->client->getImageData($this->imageIdentifier));
    }

    /**
     * The client must be able to return the binary image data from a remote image using an URL
     *
     * @covers ImboClient\Client::getImageDataFromUrl
     */
    public function testCanFetchBinaryImageDataBasedOnAnImageUrlInstance() {
        $expectedData = 'someBinaryImageData';

        $imageUrl = $this->getMock('ImboClient\Url\ImageInterface');
        $imageUrl->expects($this->once())
                 ->method('getUrl')
                 ->will($this->returnValue('http://someurl'));

        $response = $this->getResponseMock();
        $response->expects($this->once())
                 ->method('getBody')
                 ->will($this->returnValue($expectedData));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with('http://someurl')
                     ->will($this->returnValue($response));

        $this->assertSame($expectedData, $this->client->getImageDataFromUrl($imageUrl));
    }

    /**
     * Server URLs data provider
     *
     * @return array
     */
    public function getServerUrls() {
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
     * The client must be able to parse different URLs
     *
     * @dataProvider getServerUrls()
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::getServerUrls
     * @covers ImboClient\Client::parseUrls
     */
    public function testAcceptsDifferentTypesOfHostUrlsInTheConstructor($url, $expected) {
        $client = new Client($url, 'publicKey', 'privateKey');
        $urls = $client->getServerUrls();

        $this->assertInternalType('array', $urls);
        $this->assertCount(1, $urls);
        $this->assertSame($expected, $urls[0]);
    }

    /**
     * @return array[]
     */
    public function getImageResponseHeaders() {
        return array(
            array(200, 100, 400, 'image/png', 'png'),
            array(200, 100, 400, null, null), // In case the server does not yet have this feature
        );
    }

    /**
     * The client must be able to return image properties of an existing remote image
     *
     * @dataProvider getImageResponseHeaders
     * @covers ImboClient\Client::getImageProperties
     * @covers ImboClient\Client::headImage
     */
    public function testCanReturnImagePropertiesOfAnExistingImage($width, $height, $size, $mime, $extension) {
        $imageIdentifier = '8f552ba2a350be7ac19399365a738202';

        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');
        $headers->expects($this->any())->method('get')->will($this->returnCallback(function ($key) use ($width, $height, $size, $mime, $extension) {
            switch ($key) {
                case 'x-imbo-originalwidth': return $width; break;
                case 'x-imbo-originalheight': return $height; break;
                case 'x-imbo-originalfilesize': return $size; break;
                case 'x-imbo-originalmimetype': return $mime; break;
                case 'x-imbo-originalextension': return $extension; break;
            }
        }));

        $response = $this->getResponseMock();
        $response->expects($this->once())->method('getHeaders')->will($this->returnValue($headers));

        $this->driver->expects($this->once())->method('head')->will($this->returnValue($response));

        $properties = $this->client->getImageProperties($imageIdentifier);

        $this->assertInternalType('array', $properties);

        $this->assertArrayHasKey('width', $properties);
        $this->assertArrayHasKey('height', $properties);
        $this->assertArrayHasKey('filesize', $properties);
        $this->assertArrayHasKey('mimetype', $properties);
        $this->assertArrayHasKey('extension', $properties);

        $this->assertSame($width, $properties['width']);
        $this->assertSame($height, $properties['height']);
        $this->assertSame($size, $properties['filesize']);
        $this->assertSame($mime, $properties['mimetype']);
        $this->assertSame($extension, $properties['extension']);
    }

    /**
     * Get image paths and MD5 sums
     *
     * @return array[]
     */
    public function getImageIdentifiers() {
        return array(
            array(__DIR__ . '/_files/image.png', '929db9c5fc3099f7576f5655207eba47'),
            array(__DIR__ . '/_files/image.jpg', 'f3210f1bb34bfbfa432cc3560be40761'),
            array(__DIR__ . '/_files/image.gif', '09b82c817eaeda6f178572d3fb93fcb1'),
        );
    }

    /**
     * The client must be able to generate an image identifier using a valid local image
     *
     * @dataProvider getImageIdentifiers
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::validateLocalFile
     * @covers ImboClient\Client::generateImageIdentifier
     */
    public function testCanGenerateAnImageIdentifierBasedOnAValidLocalImage($path, $checksum) {
        $this->assertSame($checksum, $this->client->getImageIdentifier($path));
    }

    /**
     * The client must throw an exception when trying to generate an image identifier based on a
     * non-existing local image
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File does not exist: foobar
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::validateLocalFile
     */
    public function testThrowsExceptionWhenTryingToGenerateImageIdentifierBasedOnALocalImageThatDoesNotExist() {
        $this->client->getImageIdentifier('foobar');
    }

    /**
     * The client must throw an exception when trying to generate an image identifier based on an
     * empty local image
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File is of zero length:
     * @covers ImboClient\Client::getImageIdentifier
     * @covers ImboClient\Client::validateLocalFile
     */
    public function testThrowsExceptionWhenTryingToGenerateImageIdentifierBasedOnAnEmptyLocalImage() {
        $path = __DIR__ . '/_files/emptyImage.png';
        $this->client->getImageIdentifier($path);
    }

    /**
     * The client must be able to generate an image identifier based on a string (binary image
     * data)
     *
     * @dataProvider getImageIdentifiers
     * @covers ImboClient\Client::getImageIdentifierFromString
     * @covers ImboClient\Client::generateImageIdentifier
     */
    public function testCanGenerateAnImageIdentifierBasedOnAString($path, $checksum) {
        $this->assertSame(
            $checksum,
            $this->client->getImageIdentifierFromString(file_get_contents($path))
        );
    }

    /**
     * Data provider for testing signature generation
     *
     * @return array[]
     */
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
     * The client must be able to correctly generate signatures based on the HTTP method, the URL
     * and a timestamp
     *
     * @dataProvider getSignatureData
     * @covers ImboClient\Client::generateSignature
     */
    public function testCanGenerateValidSignaturesForRemoteWriteOperations($httpMethod, $url, $timestamp, $expected) {
        $client = new Client($this->serverUrl, $this->publicKey, $this->privateKey);

        $method = new ReflectionMethod('ImboClient\Client', 'generateSignature');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($client, $httpMethod, $url, $timestamp));
    }

    /**
     * The client must be able to fetch server status
     *
     * @covers ImboClient\Client::getServerStatus
     * @covers ImboClient\Client::getStatusUrl
     */
    public function testCanGetServerStatusWhenServerRespondsWithHttp200() {
        $response = $this->getResponseMock();
        $response->expects($this->once())
                 ->method('getBody')
                 ->will($this->returnValue('{"date":"some date","database":true,"storage":true}'));

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($this->isType('string'))
                     ->will($this->returnValue($response));

        $status = $this->client->getServerStatus();

        $this->assertInternalType('array', $status);
        $this->assertTrue($status['database']);
        $this->assertTrue($status['storage']);
    }

    /**
     * The client must be able to fetch server status when the server response with HTTP 500
     * Internal Server Error
     *
     * @covers ImboClient\Client::getServerStatus
     * @covers ImboClient\Client::getStatusUrl
     */
    public function testCanGetServerStatusWhenServerRespondsWithHttp500() {
        $response = $this->getResponseMock();
        $response->expects($this->once())
                 ->method('getBody')
                 ->will($this->returnValue('{"date":"some date","database":true,"storage":false}'));

        $exception = new ServerException('Internal server error', 500);
        $exception->setResponse($response);

        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($this->isType('string'))
                     ->will($this->throwException($exception));

        $status = $this->client->getServerStatus();

        $this->assertInternalType('array', $status);
        $this->assertTrue($status['database']);
        $this->assertFalse($status['storage']);
    }

    /**
     * When trying to fetch server status and the server responds with an error (other than HTTP
     * 500 Internal Server Error) the client must throw an exception
     *
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage Bad Request
     * @expectedExceptionCode 400
     * @covers ImboClient\Client::getServerStatus
     * @covers ImboClient\Client::getStatusUrl
     */
    public function testThrowsExceptionWhenTryingToGetServerStatusAndServerRespondsWithErrorOtherThanHttp500() {
        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($this->isType('string'))
                     ->will($this->throwException(new ServerException('Bad Request', 400)));

        $this->client->getServerStatus();
    }

    /**
     * When trying to fetch server status and the server does not respond the client must throw an
     * exception
     *
     * @expectedException ImboClient\Exception\RuntimeException
     * @expectedExceptionMessage An error occured
     * @covers ImboClient\Client::getServerStatus
     * @covers ImboClient\Client::getStatusUrl
     */
    public function testThrowsExceptionWhenTryingToGetServerStatusAndServerDoesNotRespond() {
        $this->driver->expects($this->once())
                     ->method('get')
                     ->with($this->isType('string'))
                     ->will($this->throwException(new RuntimeException('An error occured')));

        $this->client->getServerStatus();
    }

    /**
     * Fetch different URLs
     *
     * @return array[]
     */
    public function getUrls() {
        return array(
            array(
                'imbo',
                array('http://imbo'),
            ),
            array(
                'http://imbo',
                array('http://imbo'),
            ),
            array(
                array(
                    'imbo',
                    'http://imbo',
                    'https://imbo',
                    'imbo2',
                ), array(
                    'http://imbo',
                    'https://imbo',
                    'http://imbo2',
                ),
            ),
        );
    }

    /**
     * The client must be able to parse different types of "lazy" URLs passed to the constructor
     *
     * @dataProvider getUrls()
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::parseUrls
     */
    public function testCanParseUrlsMissingHttp($url, $expected) {
        $client = new Client($url, $this->publicKey, $this->privateKey);

        $method = new ReflectionMethod('ImboClient\Client', 'parseUrls');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($client, $url));
    }

    /**
     * @covers ImboClient\Client::__construct
     */
    public function testUsesCurlAsADefaultDriver() {
        $client = new Client('http://host', 'publicKey', 'privateKey');

        $property = new ReflectionProperty('ImboClient\Client', 'driver');
        $property->setAccessible(true);

        $this->assertInstanceOf('ImboClient\Driver\cURL', $property->getValue($client));
    }

    /**
     * Get a mock of the response interface
     *
     * @return ImboClient\Http\Response\ResponseInterface
     */
    private function getResponseMock() {
        return $this->getMock('ImboClient\Http\Response\ResponseInterface');
    }
}
