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

namespace ImboClient\Url\Images;

/**
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
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
     * The query instance must be able to set and get the page value
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
