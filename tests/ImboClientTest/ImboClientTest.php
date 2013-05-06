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
            'baseUrl' => $this->baseUrl,
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

    /**
     * @covers ImboClient\ImboClient::getServerStatus
     */
    public function testCanFetchServerStatusWhenEverythingIsOk() {
        $this->setMockResponse($this->client, 'status_ok');
        $result = $this->client->getServerStatus();
        $this->assertTrue($result['database']);
        $this->assertTrue($result['storage']);
    }

    /**
     * @covers ImboClient\ImboClient::getServerStatus
     */
    public function testCanFetchServerStatusWhenDatabaseIsDown() {
        $this->setMockResponse($this->client, 'status_database_down');

        try {
            $this->client->getServerStatus();
            $this->fail('Client did not throw any exception');
        } catch (ServerErrorResponseException $e) {
            $result = $e->getResponse()->json();

            $this->assertSame('Database error', $e->getResponse()->getReasonPhrase());
            $this->assertFalse($result['database']);
            $this->assertTrue($result['storage']);
        }
    }

    /**
     * @covers ImboClient\ImboClient::getServerStatus
     */
    public function testCanFetchServerStatusWhenStorageIsDown() {
        $this->setMockResponse($this->client, 'status_storage_down');

        try {
            $this->client->getServerStatus();
            $this->fail('Client did not throw any exception');
        } catch (ServerErrorResponseException $e) {
            $result = $e->getResponse()->json();

            $this->assertSame('Storage error', $e->getResponse()->getReasonPhrase());
            $this->assertTrue($result['database']);
            $this->assertFalse($result['storage']);
        }
    }

    /**
     * @covers ImboClient\ImboClient::getServerStatus
     */
    public function testCanFetchServerStatusWhenDatabaseAndStorageIsDown() {
        $this->setMockResponse($this->client, 'status_database_and_storage_down');

        try {
            $this->client->getServerStatus();
            $this->fail('Client did not throw any exception');
        } catch (ServerErrorResponseException $e) {
            $result = $e->getResponse()->json();

            $this->assertSame('Database and storage error', $e->getResponse()->getReasonPhrase());
            $this->assertFalse($result['database']);
            $this->assertFalse($result['storage']);
        }
    }

    /**
     * @covers ImboClient\ImboClient::getUserInfo
     */
    public function testCanFetchUserInformation() {
        $this->setMockResponse($this->client, 'user_ok');
        $result = $this->client->getUserInfo();
        $this->assertSame('christer', $result['publicKey']);
        $this->assertSame(11, $result['numImages']);
        $this->assertSame('Tue, 09 Apr 2013 07:00:18 GMT', $result['lastModified']);
    }
}
