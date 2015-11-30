<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClientTest;

use ImboClient\Query;

/**
 * @package Test suite
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @covers ImboClient\Query
 */
class QueryTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Query
     */
    private $query;

    /**
     * Set up the query instance
     */
    public function setUp() {
        $this->query = new Query();
    }

    /**
     * Tear down the query instance
     */
    public function tearDown() {
        $this->query = null;
    }

    public function testCanSetAndGetThePage() {
        $this->assertSame(1, $this->query->page());
        $this->assertSame($this->query, $this->query->page(10));
        $this->assertSame(10, $this->query->page());
    }

    public function testCanSetAndGetTheLimit() {
        $this->assertSame(20, $this->query->limit());
        $this->assertSame($this->query, $this->query->limit(10));
        $this->assertSame(10, $this->query->limit());
    }
}
