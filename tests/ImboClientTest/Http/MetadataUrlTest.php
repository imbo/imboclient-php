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

use ImboClient\Http\MetadataUrl;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers ImboClient\Http\MetadataUrl
 */
class MetadataUrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * Data provider
     *
     * @return array[]
     */
    public function getMetadataUrls() {
        return array(
            'no extension' => array('http://imbo/users/christer/images/image/metadata', 'christer', 'image'),
            'extension (json)' => array('http://imbo/users/christer/images/image/metadata.json', 'christer', 'image'),
            'extension (xml)' => array('http://imbo/users/christer/images/image/metadata.xml', 'christer', 'image'),
            'URL with path prefix' => array('http://imbo/some_prefix/users/christer/images/image/metadata', 'christer', 'image'),
            'missing user' => array('http://imbo/stats.json', null, null),
            'missing image identifier' => array('http://imbo/users/christer/images.json', 'christer', null),
        );
    }

    /**
     * @dataProvider getMetadataUrls
     */
    public function testCanFetchTheUserAndTheImageIdentifierInTheUrl($url, $user, $imageIdentifier) {
        $imageUrl = MetadataUrl::factory($url);
        $this->assertSame($user, $imageUrl->getUser(), 'Could not correctly identify the user in the URL');
        $this->assertSame($imageIdentifier, $imageUrl->getImageIdentifier(), 'Could not correctly identify the image identifier in the URL');
    }
}
