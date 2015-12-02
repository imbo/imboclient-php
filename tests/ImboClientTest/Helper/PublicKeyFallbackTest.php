<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClientTest\Helper;

use ImboClient\Helper\PublicKeyFallback;

/**
 * @package Test suite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @covers ImboClient\Helper\PublicKeyFallback
 */
class PublicKeyFallbackTest extends \PHPUnit_Framework_TestCase {
    public function testFallsbackToPublicKey() {
        $input = array('foo' => 'bar', 'publicKey' => 'espenh');
        $output = PublicKeyFallback::fallback($input);

        $this->assertSame('espenh', $output['user']);
        $this->assertSame('espenh', $output['publicKey']);
    }

    public function testProvidesBackwardsCompatibility() {
        $input = array('foo' => 'bar', 'user' => 'espenh');
        $output = PublicKeyFallback::fallback($input);

        $this->assertSame('espenh', $output['user']);
        $this->assertSame('espenh', $output['publicKey']);
    }
}
