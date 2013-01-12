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
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class MetadataTest extends \PHPUnit_Framework_TestCase {
    /**
     * Fetch URL data
     *
     * @return array
     */
    public function getUrlData() {
        return array(
            array('http://imbo', 'publicKey', 'image', 'http://imbo/users/publicKey/images/image/meta.json'),
        );
    }

    /**
     * The metadata URL must be able to generate a complete URL with an access token appended
     *
     * @dataProvider getUrlData
     * @covers ImboClient\Url\Url::getUrl
     * @covers ImboClient\Url\Metadata::getResourceUrl
     * @covers ImboClient\Url\Metadata::__construct
     */
    public function testCanGenerateACompleteUrlIncludingAnAccessToken($host, $publicKey, $image, $expected) {
        $url = new Metadata($host, $publicKey, 'privateKey', $image);
        $this->assertStringStartsWith($expected, $url->getUrl());
        $this->assertRegExp('/accessToken=[a-f0-9]{64}$/', $url->getUrl());
    }
}
