<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Url;

/**
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
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
