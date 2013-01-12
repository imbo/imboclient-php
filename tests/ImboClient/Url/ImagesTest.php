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
class ImagesTest extends \PHPUnit_Framework_TestCase {
    /**
     * Fetch URL data
     *
     * @return array[]
     */
    public function getUrlData() {
        return array(
            array('http://imbo', 'publicKey', 'http://imbo/users/publicKey/images.json'),
            array('http://imbo:6081', 'foobar', 'http://imbo:6081/users/foobar/images.json'),
            array('https://imbo:6081/prefix', 'foobar', 'https://imbo:6081/prefix/users/foobar/images.json'),
        );
    }

    /**
     * The images URL must be able to generate a complete URL with an access token appended
     *
     * @dataProvider getUrlData
     * @covers ImboClient\Url\Url::__construct
     * @covers ImboClient\Url\Url::getUrl
     * @covers ImboClient\Url\Images::getResourceUrl
     */
    public function testCanGenerateACompleteUrlIncludingAnAccessToken($host, $publicKey, $expected) {
        $url = new Images($host, $publicKey, 'privateKey');
        $url = $url->getUrl();
        $this->assertStringStartsWith($expected, $url);
        $this->assertRegExp('/accessToken=[a-f0-9]{64}$/', $url);
    }
}
