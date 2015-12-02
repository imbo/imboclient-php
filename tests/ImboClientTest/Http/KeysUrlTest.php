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

use ImboClient\Http\KeysUrl;

/**
 * @package Test suite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @covers ImboClient\Http\KeysUrl
 */
class KeysUrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * Data provider
     *
     * @return array[]
     */
    public function getKeysUrls() {
        return array(
            'no extension' => array('http://imbo/keys'),
            'extension (json)' => array('http://imbo/keys.json'),
            'extension (xml)' => array('http://imbo/keys.xml'),
            'URL with path prefix' => array('http://imbo/some_prefix/keys.xml'),
        );
    }

    /**
     * @dataProvider getKeysUrls
     */
    public function testCanCreateTheUrl($url) {
        $keysUrl = KeysUrl::factory($url);
        $this->assertSame($url, (string) $keysUrl);
    }
}
