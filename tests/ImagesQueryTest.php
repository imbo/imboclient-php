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

use ImboClient\ImagesQuery;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers ImboClient\ImagesQuery
 */
class ImagesQueryTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ImagesQuery
     */
    private $query;

    /**
     * Set up the query instance
     */
    public function setUp() {
        $this->query = new ImagesQuery();
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

    public function testCanSetAndGetTheMetadataFlag() {
        $this->assertFalse($this->query->metadata());
        $this->assertSame($this->query, $this->query->metadata(true));
        $this->assertTrue($this->query->metadata());
    }

    public function testCanSetAndGetFrom() {
        $now = time();
        $this->assertNull($this->query->from());
        $this->assertSame($this->query, $this->query->from($now));
        $this->assertSame($now, $this->query->from());
    }

    public function testCanSetAndGetTo() {
        $now = time();
        $this->assertNull($this->query->to());
        $this->assertSame($this->query, $this->query->to($now));
        $this->assertSame($now, $this->query->to());
    }

    public function testCanSetAndGetFields() {
        $this->assertSame(array(), $this->query->fields());
        $this->assertSame($this->query, $this->query->fields(array('size', 'width')));
        $this->assertSame(array('size', 'width'), $this->query->fields());
    }

    public function testCanSetAndGetSort() {
        $this->assertSame(array(), $this->query->sort());
        $this->assertSame($this->query, $this->query->sort(array('size', 'width')));
        $this->assertSame(array('size', 'width'), $this->query->sort());
    }

    public function testCanSetAndGetIds() {
        $this->assertSame(array(), $this->query->ids());
        $this->assertSame($this->query, $this->query->ids(array('id1', 'id2')));
        $this->assertSame(array('id1', 'id2'), $this->query->ids());
    }

    public function testCanSetAndGetChecksums() {
        $this->assertSame(array(), $this->query->checksums());
        $this->assertSame($this->query, $this->query->checksums(array('checksum1', 'checksum2')));
        $this->assertSame(array('checksum1', 'checksum2'), $this->query->checksums());
    }

    public function testCanSetAndGetOriginalChecksums() {
        $this->assertSame(array(), $this->query->originalChecksums());
        $this->assertSame($this->query, $this->query->originalChecksums(array('checksum1', 'checksum2')));
        $this->assertSame(array('checksum1', 'checksum2'), $this->query->originalChecksums());
    }
}
