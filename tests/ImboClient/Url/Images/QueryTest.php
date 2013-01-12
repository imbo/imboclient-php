<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Url\Images;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class QueryTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ImboClient\Url\Images\Query
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

    /**
     * The default page value is 1
     *
     * @covers ImboClient\Url\Images\Query::page
     */
    public function testSetsADefaultPageValueOf1() {
        $this->assertSame(1, $this->query->page());
    }

    /**
     * The default limit is 20
     *
     * @covers ImboClient\Url\Images\Query::limit
     */
    public function testSetsADefaultLimitValueOf20() {
        $this->assertSame(20, $this->query->limit());
    }

    /**
     * @covers ImboClient\Url\Images\Query::returnMetadata
     */
    public function testSetsADefaultReturnmetadataValueOfFalse() {
        $this->assertFalse($this->query->returnMetadata());
    }

    /**
     * @covers ImboClient\Url\Images\Query::metadataQuery
     */
    public function testSetsAnEmptyDefaultMetadataQuery() {
        $this->assertSame(array(), $this->query->metadataQuery());
    }

    /**
     * @covers ImboClient\Url\Images\Query::from
     */
    public function testSetsADefaultFromValueOfNull() {
        $this->assertNull($this->query->from());
    }

    /**
     * @covers ImboClient\Url\Images\Query::to
     */
    public function testSetsADefaultToValueOfNull() {
        $this->assertNull($this->query->to());
    }

    /**
     * The query instance must be able to set and get the page value
     *
     * @covers ImboClient\Url\Images\Query::page
     */
    public function testCanSetAndGetThePageValue() {
        $this->assertSame($this->query, $this->query->page(2));
        $this->assertSame(2, $this->query->page());
    }

    /**
     * The query instance must be able to set and get the limit value
     *
     * @covers ImboClient\Url\Images\Query::limit
     */
    public function testCanSetAndGetTheLimitValue() {
        $this->assertSame($this->query, $this->query->limit(30));
        $this->assertSame(30, $this->query->limit());
    }

    /**
     * @covers ImboClient\Url\Images\Query::returnMetadata
     */
    public function testCanSetAndGetTheReturnmetadataValue() {
        $this->assertSame($this->query, $this->query->returnMetadata(true));
        $this->assertTrue($this->query->returnMetadata());
    }

    /**
     * @covers ImboClient\Url\Images\Query::metadataQuery
     */
    public function testCanSetAndGetAMetadataQuery() {
        $value = array('category' => 'some category');
        $this->assertSame($this->query, $this->query->metadataQuery($value));
        $this->assertSame($value, $this->query->metadataQuery());
    }

    /**
     * @covers ImboClient\Url\Images\Query::from
     */
    public function testCanSetAndGetTheFromValue() {
        $value = 123123123;
        $this->assertSame($this->query, $this->query->from($value));
        $this->assertSame($value, $this->query->from());
    }

    /**
     * @covers ImboClient\Url\Images\Query::to
     */
    public function testCanSetAndGetTheToValue() {
        $value = 123123123;
        $this->assertSame($this->query, $this->query->to($value));
        $this->assertSame($value, $this->query->to());
    }
}
