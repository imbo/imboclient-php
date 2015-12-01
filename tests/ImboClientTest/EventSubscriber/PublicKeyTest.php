<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClientTest\EventSubscriber;

use ImboClient\EventSubscriber\PublicKey,
    Guzzle\Common\Event,
    Guzzle\Http\Message\Request;

/**
 * @package Test suite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @covers ImboClient\EventSubscriber\PublicKey
 */
class PublicKeyTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var PublicKey
     */
    private $subscriber;

    /**
     * Set up the subscriber
     */
    public function setUp() {
        $this->subscriber = new PublicKey();
    }

    /**
     * Tear down the subscriber
     */
    public function tearDown() {
        $this->subscriber = null;
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getCommands() {
        return array(
            array('user', 'publicKey', true),
            array('user', 'user', false),
            array('user', 'publicKey', true),
            array('user', 'user', false),
            array('user', 'publicKey', true),

            array('user', 'publicKey', true),
            array('user', 'publicKey', true),
            array('user', 'publicKey', true),
            array('user', 'publicKey', true),
            array('user', 'publicKey', true),
        );
    }

    public function testSubscribedEventsIsAnArray() {
        $this->assertInternalType('array', PublicKey::getSubscribedEvents());
    }

    /**
     * @dataProvider getCommands
     */
    public function testAddsPublicKeyIfUserAndPublicKeyDiffers($user, $publicKey, $shouldAdd) {
        $client = $this->getMockBuilder('ImboClient\ImboClient')->disableOriginalConstructor()->getMock();
        $client->expects($this->once())->method('getUser')->will($this->returnValue($user));
        $client->expects($this->once())->method('getPublicKey')->will($this->returnValue($publicKey));

        $query = $this->getMock('Guzzle\Http\QueryString');
        $query->expects($this->once())->method('hasKey')->with('publicKey')->will($this->returnValue(false));

        $url = $this->getMockBuilder('Guzzle\Http\Url')->disableOriginalConstructor()->getMock();
        $url->expects($this->any())->method('getQuery')->will($this->returnValue($query));

        $request = $this->getMockBuilder('Guzzle\Http\Message\Request')->disableOriginalConstructor()->getMock();
        $request->expects($this->once())->method('getClient')->will($this->returnValue($client));
        $request->expects($this->once())->method('getUrl')->will($this->returnValue($url));

        $command = $this->getMock('Guzzle\Service\Command\CommandInterface');
        $command->expects($this->any())->method('getRequest')->will($this->returnValue($request));

        if (!$shouldAdd) {
            $request->expects($this->never())->method('setHeader');
        } else {
            $request->expects($this->once())->method('setHeader')->with('X-Imbo-PublicKey', $publicKey);
        }

        $event = new Event();
        $event['command'] = $command;

        $this->subscriber->addPublicKey($event);
    }

    public function testFallsBackIfPublicKeyInQueryString() {
        $query = $this->getMock('Guzzle\Http\QueryString');
        $query->expects($this->once())->method('hasKey')->with('publicKey')->will($this->returnValue(true));

        $url = $this->getMockBuilder('Guzzle\Http\Url')->disableOriginalConstructor()->getMock();
        $url->expects($this->any())->method('getQuery')->will($this->returnValue($query));

        $request = $this->getMockBuilder('Guzzle\Http\Message\Request')->disableOriginalConstructor()->getMock();
        $request->expects($this->once())->method('getUrl')->will($this->returnValue($url));

        $command = $this->getMock('Guzzle\Service\Command\CommandInterface');
        $command->expects($this->any())->method('getRequest')->will($this->returnValue($request));

        $request->expects($this->never())->method('setHeader');

        $event = new Event();
        $event['command'] = $command;

        $this->subscriber->addPublicKey($event);
    }
}
