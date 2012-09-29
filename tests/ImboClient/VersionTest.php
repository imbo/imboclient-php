<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient;

use ReflectionProperty;

/**
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
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
        $this->assertSame('dev', $this->version->getVersionNumber());
        $this->assertSame('ImboClient-php-dev', $this->version->getVersionString());
    }

    /**
     * The version component must be able to report the correct version after the internal property has been changed. This is done by the PEAR installer when the package is installed.
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
