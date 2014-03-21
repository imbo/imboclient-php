<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClientTest;

use ImboClient\ImboClient,
    ImboClient\ImagesQuery,
    Guzzle\Http\Url,
    Guzzle\Http\Message\Response,
    Guzzle\Tests\GuzzleTestCase,
    Guzzle\Http\Exception\ServerErrorResponseException;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers ImboClient\ImboClient
 */
class ImboClientTest extends GuzzleTestCase {
    /**
     * @var ImboClient
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl = 'http://imbo';

    /**
     * @var string
     */
    private $publicKey = 'christer';

    /**
     * @var string
     */
    private $privateKey = 'test';

    /**
     * Set up the client
     */
    public function setUp() {
        $config = array(
            'serverUrls' => array($this->baseUrl),
            'publicKey' => $this->publicKey,
            'privateKey' => $this->privateKey,
        );

        $this->client = ImboClient::factory($config);
    }

    /**
     * Tear down the client
     */
    public function tearDown() {
        $this->client = null;
    }

    public function testCanFetchThePublicKeyOfTheCurrentUser() {
        $this->assertSame($this->publicKey, $this->client->getPublicKey());
    }

    public function testCanFetchServerStatusWhenEverythingIsOk() {
        $this->setMockResponse($this->client, 'status_ok');

        $status = $this->client->getServerStatus();
        $this->assertInstanceOf('DateTime', $status['date']);
        $this->assertSame('2013-04-30 06:01:19', $status['date']->format('Y-m-d H:i:s'));
        $this->assertTrue($status['database']);
        $this->assertTrue($status['storage']);
    }

    public function testCanFetchServerStatusWhenDatabaseIsDown() {
        $this->setMockResponse($this->client, 'status_database_down');

        $status = $this->client->getServerStatus();

        $this->assertFalse($status['database']);
        $this->assertTrue($status['storage']);
        $this->assertSame(500, $status['status']);
        $this->assertSame('Database error', $status['message']);
    }

    public function testCanFetchServerStatusWhenStorageIsDown() {
        $this->setMockResponse($this->client, 'status_storage_down');

        $status = $this->client->getServerStatus();

        $this->assertTrue($status['database']);
        $this->assertFalse($status['storage']);
        $this->assertSame(500, $status['status']);
        $this->assertSame('Storage error', $status['message']);
    }

    public function testCanFetchServerStatusWhenDatabaseAndStorageIsDown() {
        $this->setMockResponse($this->client, 'status_database_and_storage_down');

        $status = $this->client->getServerStatus();

        $this->assertFalse($status['database']);
        $this->assertFalse($status['storage']);
        $this->assertSame(500, $status['status']);
        $this->assertSame('Database and storage error', $status['message']);
    }

    public function testCanFetchUserInformation() {
        $this->setMockResponse($this->client, 'user_ok');

        $user = $this->client->getUserInfo();

        $this->assertSame('christer', $user['publicKey']);
        $this->assertSame(11, $user['numImages']);
        $this->assertInstanceOf('DateTime', $user['lastModified']);
        $this->assertSame('2013-04-09 07:00:18', $user['lastModified']->format('Y-m-d H:i:s'));
    }

    public function getUrlMethods() {
        return array(
            'status' => array('getStatusUrl', 'ImboClient\Http\StatusUrl'),
            'stats' => array('getStatsUrl', 'ImboClient\Http\StatsUrl'),
            'user' => array('getUserUrl', 'ImboClient\Http\UserUrl'),
            'images' => array('getImagesUrl', 'ImboClient\Http\ImagesUrl'),
        );
    }

    /**
     * @dataProvider getUrlMethods
     */
    public function testCanCreateImboUrls($method, $class) {
        $this->assertInstanceOf($class, $this->client->$method());
    }

    public function testCanCreateImageUrls() {
        $this->assertInstanceOf('ImboClient\Http\ImageUrl', $this->client->getImageUrl('identifier'));
    }

    public function testCanCreateMetadataUrls() {
        $this->assertInstanceOf('ImboClient\Http\MetadataUrl', $this->client->getMetadataUrl('identifier'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File does not exist: /foo/bar/image.png
     */
    public function testThrowsExceptionWhenTryingToAddANonExistingLocalImage() {
        $this->client->addImage('/foo/bar/image.png');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File is of zero length:
     */
    public function testThrowsExceptionWhenTryingToAddAnEmptyLocalImage() {
        $this->client->addImage(__DIR__ . '/_files/emptyImage.png');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Specified image is empty
     */
    public function testThrowsExceptionWhenTryingToAddImageFromStringAndStringIsEmpty() {
        $this->client->addImageFromsTring('');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage URL is missing scheme: /some/path
     */
    public function testThrowsExceptionWhenAddingImageFromUrlAndUrlIsInvalid() {
        $this->client->addImageFromUrl(Url::factory('/some/path'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage serverUrls must be an array
     */
    public function testFactoryThrowsAnExceptionWhenServerUrlsIsInvalid() {
        ImboClient::factory(array('serverUrls' => 'http://imbo'));
    }

    public function testCanCreateAnInstanceOfTheClientWithNoServerUrlsDefined() {
        $client = new ImboClient('http://imbo', array('publicKey' => 'public', 'privateKey' => 'private'));
        $this->assertSame(array('http://imbo'), $client->getServerUrls());
    }

    /**
     * Server URLs data provider
     *
     * @return array
     */
    public function getServerUrls() {
        return array(
            array('imbo', 'http://imbo'),

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
     * @dataProvider getServerUrls
     */
    public function testAcceptsDifferentTypesOfHostUrlsInTheConstructor($url, $expected) {
        $client = new ImboClient($url, array('publicKey' => 'public', 'privateKey' => 'private'));
        $urls = $client->getServerUrls();

        $this->assertInternalType('array', $urls);
        $this->assertCount(1, $urls);
        $this->assertSame($expected, $urls[0]);
    }

    public function testCanAddAnImageFromALocalPath() {
        $this->setMockResponse($this->client, 'image_created');

        $response = $this->client->addImage(__DIR__ . '/_files/image.png');
        $this->assertSame('929db9c5fc3099f7576f5655207eba47', $response['imageIdentifier']);
        $this->assertSame(665, $response['width']);
        $this->assertSame(463, $response['height']);
        $this->assertSame('png', $response['extension']);
        $this->assertSame(201, $response['status']);
    }

    public function testCanAddAnImageFromAUrl() {
        $this->setMockResponse($this->client, array(
            new Response(200, array(), file_get_contents(__DIR__ . '/_files/image.png')),
            'image_created',
        ));

        $response = $this->client->addImageFromUrl('http://url/to/image.png');
        $this->assertSame('929db9c5fc3099f7576f5655207eba47', $response['imageIdentifier']);
        $this->assertSame(665, $response['width']);
        $this->assertSame(463, $response['height']);
        $this->assertSame('png', $response['extension']);
        $this->assertSame(201, $response['status']);
    }

    /**
     * @expectedException Guzzle\Http\Exception\ClientErrorResponseException
     * @expectedExceptionMessage Not Found
     */
    public function testThrowsAnExceptionWhenTryingToAddAnImageFromAUrlThatResultsInAnError() {
        $this->setMockResponse($this->client, array(new Response(404)));
        $this->client->addImageFromUrl('http://url/to/image.png');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter must be a string or an instance of Guzzle\Http\Url
     */
    public function testThrowsAnExceptionWhenTryingToAddAnImageFromAUrlAndTheUrlParameterIsInvalid() {
        $this->client->addImageFromUrl(new \stdClass());
    }

    public function testCanDeleteImages() {
        $this->setMockResponse($this->client, 'image_deleted');
        $response = $this->client->deleteImage('identifier');
        $this->assertSame('929db9c5fc3099f7576f5655207eba47', $response['imageIdentifier']);
    }

    public function testCanFetchImageProperties() {
        $this->setMockResponse($this->client, 'image_properties');
        $response = $this->client->getImageProperties('identifier');
        $this->assertSame(200, $response['width']);
        $this->assertSame(300, $response['height']);
        $this->assertSame(400, $response['filesize']);
        $this->assertSame('png', $response['extension']);
        $this->assertSame('image/png', $response['mimetype']);
    }

    public function testCanEditMetadata() {
        $this->setMockResponse($this->client, 'metadata_edit');
        $response = $this->client->editMetadata('identifier', array('key' => 'value'));
        $this->assertSame(array('key' => 'value'), $response);
    }

    public function testCanReplaceMetadata() {
        $this->setMockResponse($this->client, 'metadata_replace');
        $response = $this->client->replaceMetadata('identifier', array('key' => 'othervalue'));
        $this->assertSame(array('key' => 'othervalue'), $response);
    }

    public function testCanFetchMetadata() {
        $this->setMockResponse($this->client, 'metadata_get');
        $response = $this->client->getMetadata('identifier');
        $this->assertSame(array('some' => 'metadata'), $response);
    }

    public function testCanDeleteMetadata() {
        $this->setMockResponse($this->client, 'metadata_delete');
        $response = $this->client->deleteMetadata('identifier');
        $this->assertSame(array(), $response);
    }

    public function testCanGetImages() {
        $this->setMockResponse($this->client, 'images_get');
        $response = $this->client->getImages();

        $this->assertSame(2, $response['search']['hits']);
        $this->assertSame(1, $response['search']['page']);
        $this->assertSame(20, $response['search']['limit']);
        $this->assertSame(2, $response['search']['count']);

        $this->assertCount(2, $response['images']);
        $this->assertSame('d6c335a9e0ba3aa485942925ca5ec9cd', $response['images'][0]['imageIdentifier']);
        $this->assertSame('29f7a5488303927ca345416e22f8836e', $response['images'][1]['imageIdentifier']);
    }

    public function testCanGetImagesUsingAQueryObject() {
        $this->setMockResponse($this->client, 'images_get');

        $query = new ImagesQuery();
        $query->page(2)
              ->limit(5)
              ->metadata(true)
              ->from(123)
              ->to(456)
              ->fields(array('width'))
              ->sort(array('size'))
              ->ids(array('id1', 'id2'))
              ->checksums(array('checksum1', 'checksum2'))
              ->originalChecksums(array('checksum3', 'checksum4'));

        $response = $this->client->getImages($query);

        $requests = $this->getMockedRequests();
        $request = $requests[0];
        $this->assertSame('http://imbo/users/christer/images.json?page=2&limit=5&metadata=1&from=123&to=456&fields[0]=width&sort[0]=size&ids[0]=id1&ids[1]=id2&checksums[0]=checksum1&checksums[1]=checksum2&originalChecksums[0]=checksum3&originalChecksums[1]=checksum4&accessToken=8543972a575f42c1a6d380fd6fef033bec5e9af52042bb19ced45e87f4a7046f', urldecode($request->getUrl()));
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getImageIdentifiers() {
        return array(
            array('fe0fff895d8d7ca654a1ecb0aff67c67', 'imbo5'),
            array('a76ff2e210cb559a94798c1ed0e335d3', 'imbo3'),
            array('8d2d7a2550907b85d88e4cbdf666c20e', 'imbo2'),
            array('acfb53930bd4a7489f5e761479e5e2f4', 'imbo3'),
            array('aef2bf39109708ef36de531ff1a0fc38', 'imbo5'),
            array('82f82adc4ce56e78ad88e32acd0acc4c', 'imbo1'),
        );
    }

    /**
     * @dataProvider getImageIdentifiers
     */
    public function testFetchesTheCorrectUrlForAnImageIdentifier($imageIdentifier, $expectedHost) {
        $this->client->setServerUrls(array(
            'http://imbo1',
            'http://imbo2',
            'http://imbo3',
            'http://imbo4',
            'http://imbo5',
        ));
        $url = $this->client->getImageUrl($imageIdentifier);
        $this->assertSame($expectedHost, $url->getHost());
    }

    public function testCanGetTheNumberOfImagesOfTheCurrentUser() {
        $this->setMockResponse($this->client, 'user_ok');
        $this->assertSame(11, $this->client->getNumImages());
    }

    public function testCanCheckIfALocalImageExistsOnTheServer() {
        $this->setMockResponse($this->client, array(
            'image_exists',
            'images_empty_collection',
        ));

        $this->assertTrue($this->client->imageExists(__DIR__ . '/_files/image.png'));
        $this->assertFalse($this->client->imageExists(__DIR__ . '/_files/image.jpg'));

        $requests = $this->getMockedRequests();
        $query = $requests[0]->getQuery()->toArray();
        $this->assertSame(1, $query['page']);
        $this->assertSame(1, $query['limit']);
        $this->assertSame(array(md5_file(__DIR__ . '/_files/image.png')), $query['originalChecksums']);

        $query = $requests[1]->getQuery()->toArray();
        $this->assertSame(1, $query['page']);
        $this->assertSame(1, $query['limit']);
        $this->assertSame(array(md5_file(__DIR__ . '/_files/image.jpg')), $query['originalChecksums']);
    }

    public function testCanCheckIfAnImageExistsOnTheServerBySpecifyingAnImageIdentifier() {
        $this->setMockResponse($this->client, array(
            'image_properties',
            'image_does_not_exist',
        ));

        $this->assertTrue($this->client->imageIdentifierExists('id1'));
        $this->assertFalse($this->client->imageIdentifierExists('id2'));
    }

    /**
     * @expectedException Guzzle\Http\Exception\ServerErrorResponseException
     * @expectedExceptionMessage Server error response
     */
    public function testWillRethrowExceptionWhenCheckingIfAnImageIdentifierExistsAndTheServerResponseWithAServerError() {
        $this->setMockResponse($this->client, 'server_error');
        $this->client->imageIdentifierExists('id1');
    }

    public function testCanGetImageDataBasedOnAnImageIdentifier() {
        $blob = file_get_contents(__DIR__ . '/_files/image.png');
        $this->setMockResponse($this->client, array(
            new Response(200, array(), $blob),
        ));

        $this->assertSame($blob, $this->client->getImageData('id'));
    }

    public function testCanGetImageDataBasedOnAnImageUrl() {
        $blob = file_get_contents(__DIR__ . '/_files/image.png');
        $this->setMockResponse($this->client, array(
            new Response(200, array(), $blob),
        ));

        $this->assertSame($blob, $this->client->getImageDataFromUrl($this->client->getImageUrl('id')));
    }

    public function testCanGetServerStatistics() {
        $this->setMockResponse($this->client, 'stats_get');
        $response = $this->client->getServerStats();
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('users', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('custom', $response);
    }

    public function testCanGenerateAShortUrl() {
        $this->setMockResponse($this->client, 'shorturl_created');
        $imageUrl = $this->client->getImageUrl('image')->thumbnail()->desaturate()->jpg();
        $response = $this->client->generateShortUrl($imageUrl);
        $this->assertSame('aaaaaaa', $response['id']);
        $this->assertSame(201, $response['status']);

        $requests = $this->getMockedRequests();

        $this->assertSame(
            '{"publicKey":"christer","imageIdentifier":"image","extension":"jpg","query":"?t[]=thumbnail:width=50,height=50,fit=outbound&t[]=desaturate"}',
            (string) $requests[0]->getBody(),
            'Invalid JSON-encoded data in the request body'
        );
    }

    public function testCanGenerateAShortUrlWithNoExtensionOrTransformationsAdded() {
        $this->setMockResponse($this->client, 'shorturl_created');
        $response = $this->client->generateShortUrl($this->client->getImageUrl('image'));
        $this->assertSame('aaaaaaa', $response['id']);
        $this->assertSame(201, $response['status']);

        $requests = $this->getMockedRequests();

        $this->assertSame(
            '{"publicKey":"christer","imageIdentifier":"image","extension":null,"query":null}',
            (string) $requests[0]->getBody(),
            'Invalid JSON-encoded data in the request body'
        );
    }

    public function testCanGetAShortUrl() {
        $this->setMockResponse($this->client, 'shorturl_created');
        $url = $this->client->getShortUrl($this->client->getImageUrl('image'));

        $this->assertInstanceOf('Guzzle\Http\Url', $url);
        $this->assertSame('http://imbo/s/aaaaaaa', (string) $url);
    }

    public function testCanGetAShortUrlAsAString() {
        $this->setMockResponse($this->client, 'shorturl_created');
        $url = $this->client->getShortUrl($this->client->getImageUrl('image'), true);

        $this->assertSame('http://imbo/s/aaaaaaa', $url);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Could not generate short URL
     */
    public function testThrowsExceptionWhenTryingToGetShortUrlAndGenerateShortUrlFails() {
        $this->setMockResponse($this->client, new Response(400));
        $url = $this->client->getShortUrl($this->client->getImageUrl('image'));
    }
}
