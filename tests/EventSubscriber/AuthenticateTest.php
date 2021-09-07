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

use ImboClient\EventSubscriber\Authenticate,
    Guzzle\Common\Event,
    Guzzle\Http\Message\Request;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers ImboClient\EventSubscriber\Authenticate
 */
class AuthenticateTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Autheticate
     */
    private $subscriber;

    /**
     * Set up the subscriber
     */
    public function setUp() {
        $this->subscriber = new Authenticate();
    }

    /**
     * Tear down the subscriber
     */
    public function tearDown() {
        $this->subscriber = null;
    }

    public function testSubscribedEventsIsAnArray() {
        $this->assertInternalType('array', Authenticate::getSubscribedEvents());
    }

    /**
     * Get some commands
     *
     * @return array[]
     */
    public function getCommands() {
        return array(
            array('GetUserInfo', false),
            array('GetImages', false),
            array('GetImageProperties', false),
            array('GetMetadata', false),

            array('AddImage', true),
            array('DeleteImage', true),
            array('ReplaceMetadata', true),
            array('EditMetadata', true),
            array('DeleteMetadata', true),
        );
    }

    /**
     * @dataProvider getCommands
     */
    public function testAddsauthenticateHeadersOnSomeCommands($commandName, $sign) {
        $request = $this->getMockBuilder('Guzzle\Http\Message\Request')->disableOriginalConstructor()->getMock();

        $command = $this->getMock('Guzzle\Service\Command\CommandInterface');
        $command->expects($this->once())->method('getName')->will($this->returnValue($commandName));

        if (!$sign) {
            $command->expects($this->never())->method('getRequest');
        } else {
            $client = $this->getMock('Guzzle\Http\ClientInterface');
            $client->expects($this->at(0))->method('getConfig')->with('publicKey')->will($this->returnValue('user'));
            $client->expects($this->at(1))->method('getConfig')->with('privateKey')->will($this->returnValue('private key'));

            $request->expects($this->at(0))->method('getClient')->will($this->returnValue($client));
            $request->expects($this->at(1))->method('getMethod')->will($this->returnValue('HTTP METHOD'));
            $request->expects($this->at(2))->method('getUrl')->will($this->returnValue('some url'));
            $request->expects($this->at(3))->method('setHeader')->with('X-Imbo-Authenticate-Signature', $this->isType('string'));
            $request->expects($this->at(4))->method('setHeader')->with('X-Imbo-Authenticate-Timestamp', $this->isType('string'));

            $command->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        }

        $event = new Event();
        $event['command'] = $command;

        $this->subscriber->signRequest($event);
    }
}
