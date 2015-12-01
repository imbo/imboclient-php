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

use ImboClient\Http\ResourceGroupUrl;

/**
 * @package Test suite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @covers ImboClient\Http\ResourceGroupUrl
 */
class ResourceGroupUrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * Data provider
     *
     * @return array[]
     */
    public function getGroupUrls() {
        return array(
            'no extension' => array('http://imbo/groups/foobar', 'foobar'),
            'extension (json)' => array('http://imbo/groups/foobar.json', 'foobar'),
            'extension (xml)' => array('http://imbo/groups/foobar.xml', 'foobar'),
            'URL with path prefix' => array('http://imbo/some_prefix/groups/foobar.xml', 'foobar'),
            'missing group name' => array('http://imbo/', null),
        );
    }

    /**
     * @dataProvider getGroupUrls
     */
    public function testCanFetchTheGroupInTheUrl($url, $groupName) {
        $groupUrl = ResourceGroupUrl::factory($url);
        $this->assertSame($groupName, $groupUrl->getGroup());
    }
}
