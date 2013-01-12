<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient;

use ReflectionProperty;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class VersionTest extends \PHPUnit_Framework_TestCase {
    /**
     * Client instance
     *
     * @var Version
     */
    private $version;

    /**
     * Set up the version instance
     *
     * @covers ImboClient\Client::__construct
     * @covers ImboClient\Client::setDriver
     */
    public function setUp() {
        $this->version = new Version();
    }

    /**
     * Tear down the version instance
     */
    public function tearDown() {
        $this->version = null;
    }

    /**
     * The version component reports as "dev" pr. default
     *
     * @covers ImboClient\Version::getVersionNumber
     * @covers ImboClient\Version::getVersionString
     */
    public function testReportsDevPrDefault() {
        $this->assertStringStartsWith('dev-', $this->version->getVersionNumber());
        $this->assertStringStartsWith('ImboClient-php-dev-', $this->version->getVersionString());
    }

    /**
     * The version component must be able to report the correct version after the internal property
     * has been changed.
     *
     * @covers ImboClient\Version::getVersionNumber
     * @covers ImboClient\Version::getVersionString
     */
    public function testReportsCorrectVersionAfterInternalPropertyHasBeenChanged() {
        $version = new ReflectionProperty('ImboClient\Version', 'version');
        $version->setAccessible(true);
        $version->setValue($this->version, '1.0.0');

        $this->assertSame('1.0.0', $this->version->getVersionNumber());
        $this->assertSame('ImboClient-php-1.0.0', $this->version->getVersionString());
    }
}
