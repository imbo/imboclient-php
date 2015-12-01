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

use ImboClient\Http\ResourceGroupsUrl;

/**
 * @package Test suite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @covers ImboClient\Http\ResourceGroupsUrl
 */
class ResourceGroupsUrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * Data provider
     *
     * @return array[]
     */
    public function getGroupsUrls() {
        return array(
            'no extension' => array('http://imbo/groups'),
            'extension (json)' => array('http://imbo/groups.json'),
            'extension (xml)' => array('http://imbo/groups.xml'),
            'URL with path prefix' => array('http://imbo/some_prefix/groups.xml'),
        );
    }

    /**
     * @dataProvider getGroupsUrls
     */
    public function testCanCreateTheUrl($url) {
        $groupUrl = ResourceGroupsUrl::factory($url);
        $this->assertSame($url, (string) $groupUrl);
    }
}
