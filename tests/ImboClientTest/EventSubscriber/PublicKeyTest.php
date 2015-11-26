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
            array('GetUserInfo', 'user', 'publicKey', true),
            array('GetUserInfo', 'user', 'user', false),
            array('GetImages', 'user', 'publicKey', true),
            array('GetImageProperties', 'user', 'user', false),
            array('GetMetadata', 'user', 'publicKey', true),

            array('AddImage', 'user', 'publicKey', true),
            array('DeleteImage', 'user', 'publicKey', true),
            array('ReplaceMetadata', 'user', 'publicKey', true),
            array('EditMetadata', 'user', 'publicKey', true),
            array('DeleteMetadata', 'user', 'publicKey', true),
        );
    }

    public function testSubscribedEventsIsAnArray() {
        $this->assertInternalType('array', PublicKey::getSubscribedEvents());
    }

    /**
     * @dataProvider getCommands
     */
    public function testAppendsPublicKeyOnlyForSomeCommands($commandName, $user, $publicKey, $shouldAdd) {
        $client = $this->getMockBuilder('ImboClient\ImboClient')->disableOriginalConstructor()->getMock();
        $client->expects($this->once())->method('getUser')->will($this->returnValue($user));
        $client->expects($this->once())->method('getPublicKey')->will($this->returnValue($publicKey));

        $request = $this->getMockBuilder('Guzzle\Http\Message\Request')->disableOriginalConstructor()->getMock();
        $request->expects($this->once())->method('getClient')->will($this->returnValue($client));

        $command = $this->getMock('Guzzle\Service\Command\CommandInterface');
        $command->expects($this->any())->method('getRequest')->will($this->returnValue($request));

        if (!$shouldAdd) {
            $request->expects($this->never())->method('getQuery');
            $command->expects($this->never())->method('getName');
        } else {
            $query = $this->getMock('Guzzle\Http\QueryString');
            $query->expects($this->once())->method('set')->with('publicKey', $this->isType('string'));

            $request->expects($this->once())->method('getQuery')->will($this->returnValue($query));
            $command->expects($this->once())->method('getName')->will($this->returnValue($commandName));
        }

        $event = new Event();
        $event['command'] = $command;

        $this->subscriber->appendPublicKey($event);
    }
}
