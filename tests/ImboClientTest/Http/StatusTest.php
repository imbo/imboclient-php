<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Http;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class StatusTest extends \PHPUnit_Framework_TestCase {
    /**
     * Fetch URL data
     *
     * @return array[]
     */
    public function getUrlData() {
        return array(
            array('http://imbo', 'http://imbo/status.json'),
            array('http://host/imbo', 'http://host/imbo/status.json'),
            array('http://imbo:6081', 'http://imbo:6081/status.json'),
        );
    }

    /**
     * The status URL must be able to correctly generate a complete URL with no access token
     *
     * @dataProvider getUrlData
     * @covers ImboClient\Url\Url::__construct
     * @covers ImboClient\Url\Url::getUrl
     * @covers ImboClient\Url\Status::getResourceUrl
     */
    public function testCanGenerateACompleteUrlThatDoesNotIncludeAnAccessToken($host, $expected) {
        $url = new Status($host);
        $this->assertSame($expected, $url->getUrl());
    }
}
