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
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Driver;

/**
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class CurlTest extends \PHPUnit_Framework_TestCase {
    /**
     * The driver instance
     *
     * @var ImboClient\Driver\Curl
     */
    private $driver;

    /**
     * URL to the script that the tests should send requests to
     *
     * @var string
     */
    private $testUrl;

    /**
     * Setup the driver
     */
    public function setUp() {
        if (!IMBOCLIENT_ENABLE_TESTS) {
            $this->markTestSkipped('IMBOCLIENT_ENABLE_TESTS must be set to true to run these tests');
        }

        $this->driver  = new Curl();
        $this->testUrl = IMBOCLIENT_TESTS_URL;
    }

    /**
     * Tear down the driver
     *
     * @covers ImboClient\Driver\Curl::__destruct
     */
    public function tearDown() {
        $this->driver = null;
    }

    /**
     * @covers ImboClient\Driver\Curl::post
     * @covers ImboClient\Driver\Curl::request
     */
    public function testPost() {
        $metadata = array(
            'foo' => 'bar',
            'bar' => 'foo',
        );
        $response = $this->driver->post($this->testUrl, json_encode($metadata));
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);

        $result = unserialize($response->getBody());
        $this->assertSame('POST', $result['method']);
        $this->assertSame($metadata, json_decode($result['data'], true));
    }

    /**
     * This method will PUT the current file (__FILE__) to the test script. The test script will
     * then read this file and inject the md5 sum of the file into the output. This method will
     * then compute the md5 sum and make sure it's the same as the one from the test script.
     *
     * @covers ImboClient\Driver\Curl::put
     * @covers ImboClient\Driver\Curl::request
     */
    public function testPut() {
        $url = $this->testUrl;
        $response = $this->driver->put($url, __FILE__);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $data = unserialize($response->getBody());

        $this->assertSame($data['md5'], md5_file(__FILE__));
    }

    /**
     * @covers ImboClient\Driver\Curl::putData
     * @covers ImboClient\Driver\Curl::request
     */
    public function testPutData() {
        $file = file_get_contents(__FILE__);

        $url = $this->testUrl;
        $response = $this->driver->putData($url, $file);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $data = unserialize($response->getBody());

        $this->assertSame($data['md5'], md5($file));
    }

    /**
     * @covers ImboClient\Driver\Curl::get
     * @covers ImboClient\Driver\Curl::request
     */
    public function testGet() {
        $url = $this->testUrl . '?foo=bar&bar=foo';
        $response = $this->driver->get($url);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $result = unserialize($response->getBody());
        $this->assertSame('GET', $result['method']);
        $this->assertSame(array('foo' => 'bar', 'bar' => 'foo'), $result['data']);
    }

    /**
     * @covers ImboClient\Driver\Curl::head
     * @covers ImboClient\Driver\Curl::request
     */
    public function testHead() {
        $response = $this->driver->head($this->testUrl);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $this->assertEmpty($response->getBody());
    }

    /**
     * @covers ImboClient\Driver\Curl::delete
     * @covers ImboClient\Driver\Curl::request
     */
    public function testDelete() {
        $response = $this->driver->delete($this->testUrl);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $result = unserialize($response->getBody());
        $this->assertSame('DELETE', $result['method']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedException An error occured. Request timed out during transfer (limit: 2s).
     * @covers ImboClient\Driver\Curl::get
     * @covers ImboClient\Driver\Curl::request
     */
    public function testReadTimeout() {
        $url = $this->testUrl . '?sleep=3';
        $this->driver->get($url);
    }

    /**
     * @expectedException RuntimeException
     * @expectedException An error occured. Request timed out during transfer (limit: 1s).
     * @covers ImboClient\Driver\Curl::__construct
     */
    public function testConstructWithCustomParams() {
        $params = array(
            'timeout' => 1,
        );
        $driver = new Curl($params);
        $url = $this->testUrl . '?sleep=2';
        $driver->get($url);
    }

    /**
     * @covers ImboClient\Driver\Curl::post
     * @covers ImboClient\Driver\Curl::request
     */
    public function testExpectHeaderNotPresent() {
        $postData = '{"some":"data"}';
        $url = $this->testUrl . '?headers';
        $response = $this->driver->post($url, $postData);
        $headers = unserialize($response->getBody());

        $this->assertArrayNotHasKey('HTTP_EXPECT', $headers);

        // Add a header
        $this->assertSame($this->driver, $this->driver->addRequestHeader('Header', 'value'));

        $url = $this->testUrl . '?headers';
        $response = $this->driver->post($url, $postData);
        $headers = unserialize($response->getBody());

        $this->assertArrayNotHasKey('HTTP_EXPECT', $headers);
    }

    /**
     * @covers ImboClient\Driver\Curl::addRequestHeader
     */
    public function testAddRequestHeader() {
        $this->assertSame($this->driver, $this->driver->addRequestHeader('Header', 'value'));
        $url = $this->testUrl . '?headers';
        $response = $this->driver->get($url);
        $headers = unserialize($response->getBody());

        $this->assertArrayHasKey('HTTP_HEADER', $headers);
        $this->assertSame('value', $headers['HTTP_HEADER']);
    }

    /**
     * @covers ImboClient\Driver\Curl::addRequestHeader
     * @covers ImboClient\Driver\Curl::addRequestHeaders
     */
    public function testAddRequestHeaders() {
        $this->assertSame($this->driver, $this->driver->addRequestHeaders(array(
            'Header' => 'value',
            'User-Agent' => 'ImboClient',
        )));
        $url = $this->testUrl . '?headers';
        $response = $this->driver->get($url);
        $headers = unserialize($response->getBody());

        $this->assertArrayHasKey('HTTP_HEADER', $headers);
        $this->assertArrayHasKey('HTTP_USER_AGENT', $headers);

        $this->assertSame('value', $headers['HTTP_HEADER']);
        $this->assertSame('ImboClient', $headers['HTTP_USER_AGENT']);
    }
}
