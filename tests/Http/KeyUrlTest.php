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

use ImboClient\Http\KeyUrl;

/**
 * @package Test suite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @covers ImboClient\Http\KeyUrl
 */
class KeyUrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * Data provider
     *
     * @return array[]
     */
    public function getKeyUrls() {
        return array(
            'no extension' => array('http://imbo/keys/barfoo', 'barfoo'),
            'extension (json)' => array('http://imbo/keys/barfoo.json', 'barfoo'),
            'extension (xml)' => array('http://imbo/keys/barfoo.xml', 'barfoo'),
            'URL with path prefix' => array('http://imbo/some_prefix/keys/barfoo.xml', 'barfoo'),
            'missing key name' => array('http://imbo/', null),
        );
    }

    /**
     * @dataProvider getKeyUrls
     */
    public function testCanFetchTheGroupInTheUrl($url, $groupName) {
        $keyUrl = KeyUrl::factory($url);
        $this->assertSame($groupName, $keyUrl->getKey());
    }
}
