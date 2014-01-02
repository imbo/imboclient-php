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
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Could not fetch image: http://url/to/image.png
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
        $response = $this->client->editMetadata('identifier', array('some' => 'metadata'));
        $this->assertSame('929db9c5fc3099f7576f5655207eba47', $response['imageIdentifier']);
    }

    public function testCanReplaceMetadata() {
        $this->setMockResponse($this->client, 'metadata_edit');
        $response = $this->client->replaceMetadata('identifier', array('some' => 'metadata'));
        $this->assertSame('929db9c5fc3099f7576f5655207eba47', $response['imageIdentifier']);
    }

    public function testCanFetchMetadata() {
        $this->setMockResponse($this->client, 'metadata_get');
        $response = $this->client->getMetadata('identifier');
        $this->assertSame(array('some' => 'metadata'), $response);
    }

    public function testCanDeleteMetadata() {
        $this->setMockResponse($this->client, 'metadata_delete');
        $response = $this->client->deleteMetadata('identifier');
        $this->assertSame('929db9c5fc3099f7576f5655207eba47', $response['imageIdentifier']);
    }
}
