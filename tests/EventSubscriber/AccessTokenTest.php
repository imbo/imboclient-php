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

use ImboClient\EventSubscriber\AccessToken,
    Guzzle\Common\Event,
    Guzzle\Http\Message\Request;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers ImboClient\EventSubscriber\AccessToken
 */
class AccessTokenTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var AccessToken
     */
    private $subscriber;

    /**
     * Set up the subscriber
     */
    public function setUp() {
        $this->subscriber = new AccessToken();
    }

    /**
     * Tear down the subscriber
     */
    public function tearDown() {
        $this->subscriber = null;
    }

    /**
     * Get some URL's, private keys and expected access tokens
     *
     * @return array[]
     */
    public function getUrlsForAccessTokens() {
        return array(
            array('http://imbo/users/christer.json', 'key', 'b85f9a8c2ffd2eb6a550b259e417686cf63b567b2b6972630b8e8519f6bb06b9'),
            array('http://imbo/users/christer/images.json', 'key', '83120ae2f89e9abc24cb3209aaf2793c17081c4eafdc3a548e185457f048a0d9'),
            array('http://imbo/users/christer/images/image.png', 'key', '92a4a11491f861bc5054a311f8b893cec839d938b648f45f4c706c1e04a20db4'),
            array('http://imbo/users/christer/images/image/metadata.json', 'key', 'd87c19521f4576c5d14a6eead2cb8c9270675158e02c27310d0462dde47a2ec6'),

            array('http://imbo/users/christer.json', 'otherKey', 'f6fcf509b70c90a56127a257c8b095dc54db469ba4686e55eac075372bfc54e0'),
            array('http://imbo/users/christer/images.json', 'otherKey', '679fd5406a50d39fdef2d8cc07b832ac1e0485bb995dbaa2eb6e774ca0089297'),
            array('http://imbo/users/christer/images/image.png', 'otherKey', '4aa83b42596ce342132b7872facce11b03f55a2bf7f14c45868298b670ff8e5d'),
            array('http://imbo/users/christer/images/image/metadata.json', 'otherKey', '07c0e72d0333dd7f0a7d3a11cdef49620c81e5215e535ffae9c7d8a4a50309a8'),
        );
    }

    /**
     * @dataProvider getUrlsForAccessTokens
     */
    public function testCanGetTheAccessTokenOfAUrl($url, $privateKey, $accessToken) {
        $this->assertSame($accessToken, $this->subscriber->getAccessToken($url, $privateKey));
    }

    public function testSubscribedEventsIsAnArray() {
        $this->assertInternalType('array', AccessToken::getSubscribedEvents());
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getCommands() {
        return array(
            array('GetUserInfo', true),
            array('GetImages', true),
            array('GetImageProperties', true),
            array('GetMetadata', true),

            array('AddImage', false),
            array('DeleteImage', false),
            array('ReplaceMetadata', false),
            array('EditMetadata', false),
            array('DeleteMetadata', false),
        );
    }

    /**
     * @dataProvider getCommands
     */
    public function testAppendsAnAccessTokenOnlyForSomeCommands($commandName, $accessToken) {
        $request = $this->getMockBuilder('Guzzle\Http\Message\Request')->disableOriginalConstructor()->getMock();

        if (!$accessToken) {
            $request->expects($this->never())->method('getQuery');
        } else {
            $query = $this->getMock('Guzzle\Http\QueryString');
            $query->expects($this->once())->method('set')->with('accessToken', $this->isType('string'));

            $client = $this->getMock('Guzzle\Http\ClientInterface');
            $client->expects($this->once())->method('getConfig')->with('privateKey')->will($this->returnValue('private key'));

            $request->expects($this->once())->method('getQuery')->will($this->returnValue($query));
            $request->expects($this->once())->method('getUrl')->will($this->returnValue('someurl'));
            $request->expects($this->once())->method('getClient')->will($this->returnValue($client));
        }

        $command = $this->getMock('Guzzle\Service\Command\CommandInterface');
        $command->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $command->expects($this->once())->method('getName')->will($this->returnValue($commandName));

        $event = new Event();
        $event['command'] = $command;

        $this->subscriber->appendAccessToken($event);
    }
}
