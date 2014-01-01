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
}
