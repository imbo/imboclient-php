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

use ImboClient\Http\Url;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers ImboClient\Http\Url
 */
class UrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * Get URL's
     *
     * @return array[]
     */
    public function getUrls() {
        return array(
            'with private key' => array(
                'http://imbo/users/christer.json',
                'http://imbo/users/christer.json?accessToken=b85f9a8c2ffd2eb6a550b259e417686cf63b567b2b6972630b8e8519f6bb06b9',
                'key'
            ),
            'without private key' => array(
                'http://imbo/users/christer.json',
                'http://imbo/users/christer.json'
            ),
            'with existing query param' => array(
                'http://imbo/users/christer.json?foo=bar&bar=foo',
                'http://imbo/users/christer.json?foo=bar&bar=foo&accessToken=fdf7e9ce99df3c9bf5934c591791e3513c18162317f0a71ad942cec80f7db99e',
                'key'
            ),
        );
    }

    /**
     * @dataProvider getUrls
     */
    public function testCanAddAnAccessTokenQueryParameter($url, $urlWithToken, $privateKey = null) {
        $urlInstance = Url::factory($url, $privateKey);
        $this->assertSame($urlWithToken, (string) $urlInstance);
    }

    /**
     * @see https://github.com/imbo/imboclient-php/issues/90
     */
    public function testUrlsCanGetConvertedToStringsMoreThanOnce() {
        $urlInstance = Url::factory('http://imbo/users/christer.json', 'key');

        $this->assertSame('http://imbo/users/christer.json?accessToken=b85f9a8c2ffd2eb6a550b259e417686cf63b567b2b6972630b8e8519f6bb06b9', (string) $urlInstance);
        $this->assertSame((string) $urlInstance, (string) $urlInstance);
    }

    /**
     * Get URLs with different user/public key combinations
     *
     * @return array[]
     */
    public function getUserPubKeyUrls() {
        return array(
            'Imbo 1.x compatible fallback' => array('http://imbo/users/christer.json', 'christer', 'christer'),
            'Imbo 2.x specified public key' => array('http://imbo/users/christer.json?publicKey=foo', 'christer', 'foo'),
            'URL without user' => array('http://imbo/stats.json?publicKey=foo', null, 'foo'),
        );
    }

    /**
     * @dataProvider getUserPubKeyUrls
     */
    public function testCanDifferentiateBetweenUsersAndPublicKeys($url, $user, $publicKey) {
        $urlInstance = Url::factory($url);
        $this->assertSame($user, $urlInstance->getUser());
        $this->assertSame($publicKey, $urlInstance->getPublicKey());
    }

    public function testAddsPublicKeyIfNoUserSpecified() {
        $urlInstance = Url::factory('http://imbo/groups.json', 'privkey', 'pubkey-of-win');
        $this->assertContains('publicKey=pubkey-of-win', (string) $urlInstance);
        $this->assertContains('accessToken=c357e8616ac57574e1dd670f8', (string) $urlInstance);
    }

    public function testAddsPublicKeyIfUserAndPublicKeyDiffers() {
        $urlInstance = Url::factory('http://imbo/users/foo/images', 'privkey', 'pubkey-of-win');
        $this->assertContains('publicKey=pubkey-of-win', (string) $urlInstance);
    }

    public function testDoesNotAddPublicKeyIfUserAndPublicKeyIsSame() {
        $urlInstance = Url::factory('http://imbo/users/foo/images', 'privkey', 'foo');
        $this->assertNotContains('publicKey=foo', (string) $urlInstance);
    }

    public function testOverridesPublicKeyIfDifferentPublicKeyPassed() {
        $urlInstance = Url::factory('http://imbo/users/wat/images?publicKey=omg&accessToken=wtf', 'privkey', 'foo');
        $this->assertContains('publicKey=foo', (string) $urlInstance);
        $this->assertContains('accessToken=1a60d466525d55e41fc1e2283b579fe23b846851ef7c2bb', (string) $urlInstance);
    }

    public function testRemovesPublicKeyIfUserMatchesAndPrivateKeyIsProvided() {
        $urlInstance = Url::factory('http://imbo/users/wat/images?publicKey=wat&accessToken=wtf', 'oo');
        $this->assertNotContains('publicKey=', (string) $urlInstance);
    }

    public function testDoesNotRemovePublicKeyIfNoPrivateKeyIsProvided() {
        $urlInstance = Url::factory('http://imbo/users/wat/images?publicKey=wat&accessToken=wtf');
        $this->assertContains('publicKey=wat', (string) $urlInstance);
    }
}
