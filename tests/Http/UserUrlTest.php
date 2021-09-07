<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClientTest\Http;

use ImboClient\Http\UserUrl;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers ImboClient\Http\UserUrl
 */
class UserUrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * Data provider
     *
     * @return array[]
     */
    public function getUserUrls() {
        return array(
            'no extension' => array('http://imbo/users/christer', 'christer', 'christer'),
            'extension (json)' => array('http://imbo/users/christer.json?publicKey=foo', 'christer', 'foo'),
            'extension (xml)' => array('http://imbo/users/christer.xml', 'christer', 'christer'),
            'URL with path prefix' => array('http://imbo/some_prefix/users/christer.xml?publicKey=z', 'christer', 'z'),
            'missing public key' => array('http://imbo/?publicKey=bar', null, 'bar'),
        );
    }

    /**
     * @dataProvider getUserUrls
     */
    public function testCanFetchTheUserAndPublicKeyInTheUrl($url, $user, $publicKey) {
        $userUrl = UserUrl::factory($url);
        $this->assertSame($user, $userUrl->getUser());
        $this->assertSame($publicKey, $userUrl->getPublicKey());
    }
}
