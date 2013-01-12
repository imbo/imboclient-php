<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Url;

/**
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class AccessTokenTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ImboClient\Url\AccessTokenInterface
     */
    private $accessToken;

    /**
     * Set up the access token instance
     */
    public function setUp() {
        $this->accessToken = new AccessToken();
    }

    /**
     * Tear down the access token instance
     */
    public function tearDown() {
        $this->accessToken = null;
    }

    /**
     * The access token must generate the samen token every time given the same URL and key
     *
     * @covers ImboClient\Url\AccessToken::generateToken
     */
    public function testWillGenerateTheSameKeyEveryTimeGivenTheSameUrlAndKey() {
        $url = 'http://imbo/users/user/images.json';
        $key = 'some key';

        $this->assertSame(
            $this->accessToken->generateToken($url, $key),
            $this->accessToken->generateToken($url, $key)
        );
    }

    /**
     * Fetch keys pairs
     *
     * @return array[]
     */
    public function getKeys() {
        return array(
            array('key1', 'key2'),
            array('key2', 'key3'),
            array('key3', 'key4'),
            array('key4', 'key5'),
            array('key5', 'key6'),
            array('key6', 'key7'),
            array('key7', 'key8'),
            array('key8', 'key9'),
        );
    }

    /**
     * The access token must generate different tokens given different keys
     *
     * @dataProvider getKeys
     * @covers ImboClient\Url\AccessToken::generateToken
     */
    public function testWillGenerateDifferentTokensGivenDifferentKeys($key1, $key2) {
        $url = 'http://imbo/users/user/images.json';

        $this->assertNotSame(
            $this->accessToken->generateToken($url, $key1),
            $this->accessToken->generateToken($url, $key2)
        );
    }
}
