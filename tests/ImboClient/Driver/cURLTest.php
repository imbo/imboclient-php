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

use ImboClient\Exception\ServerException,
    ReflectionClass,
    ReflectionProperty;

/**
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class cURLTest extends \PHPUnit_Framework_TestCase {
    /**
     * The driver instance
     *
     * @var ImboClient\Driver\cURL
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

        $this->driver  = new cURL();
        $this->testUrl = IMBOCLIENT_TESTS_URL;
    }

    /**
     * Tear down the driver
     *
     * @covers ImboClient\Driver\cURL::__destruct
     */
    public function tearDown() {
        $this->driver = null;
    }

    /**
     * @covers ImboClient\Driver\cURL::post
     * @covers ImboClient\Driver\cURL::request
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
     * @covers ImboClient\Driver\cURL::put
     * @covers ImboClient\Driver\cURL::request
     */
    public function testPut() {
        $url = $this->testUrl;
        $response = $this->driver->put($url, __FILE__);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $data = unserialize($response->getBody());

        $this->assertSame($data['md5'], md5_file(__FILE__));
    }

    /**
     * @covers ImboClient\Driver\cURL::putData
     * @covers ImboClient\Driver\cURL::request
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
     * @covers ImboClient\Driver\cURL::get
     * @covers ImboClient\Driver\cURL::request
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
     * @covers ImboClient\Driver\cURL::head
     * @covers ImboClient\Driver\cURL::request
     */
    public function testHead() {
        $response = $this->driver->head($this->testUrl);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $this->assertEmpty($response->getBody());
    }

    /**
     * @covers ImboClient\Driver\cURL::delete
     * @covers ImboClient\Driver\cURL::request
     */
    public function testDelete() {
        $response = $this->driver->delete($this->testUrl);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $result = unserialize($response->getBody());
        $this->assertSame('DELETE', $result['method']);
    }

    /**
     * @expectedException ImboClient\Exception\RuntimeException
     * @expectedExceptionMessage An error occured. Request timed out during transfer (limit: 2s).
     * @covers ImboClient\Driver\cURL::get
     * @covers ImboClient\Driver\cURL::request
     */
    public function testReadTimeout() {
        $url = $this->testUrl . '?sleep=3';
        $this->driver->get($url);
    }

    /**
     * @expectedException ImboClient\Exception\RuntimeException
     * @expectedExceptionMessage An error occured. Request timed out during transfer (limit: 1s).
     * @covers ImboClient\Driver\cURL::__construct
     */
    public function testConstructWithCustomParams() {
        $params = array(
            'timeout' => 1,
        );
        $driver = new cURL($params);
        $url = $this->testUrl . '?sleep=2';
        $driver->get($url);
    }

    /**
     * @covers ImboClient\Driver\cURL::post
     * @covers ImboClient\Driver\cURL::request
     */
    public function testExpectHeaderNotPresent() {
        $postData = '{"some":"data"}';
        $url = $this->testUrl . '?headers';
        $response = $this->driver->post($url, $postData);
        $headers = unserialize($response->getBody());

        $this->assertArrayNotHasKey('HTTP_EXPECT', $headers);

        // Add a header
        $this->assertSame($this->driver, $this->driver->setRequestHeader('Header', 'value'));

        $url = $this->testUrl . '?headers';
        $response = $this->driver->post($url, $postData);
        $headers = unserialize($response->getBody());

        $this->assertArrayNotHasKey('HTTP_EXPECT', $headers);
    }

    /**
     * @covers ImboClient\Driver\cURL::setRequestHeader
     */
    public function testSetRequestHeader() {
        $this->assertSame($this->driver, $this->driver->setRequestHeader('Header', 'value'));
        $url = $this->testUrl . '?headers';
        $response = $this->driver->get($url);
        $headers = unserialize($response->getBody());

        $this->assertArrayHasKey('HTTP_HEADER', $headers);
        $this->assertSame('value', $headers['HTTP_HEADER']);
    }

    /**
     * @covers ImboClient\Driver\cURL::setRequestHeader
     * @covers ImboClient\Driver\cURL::setRequestHeaders
     */
    public function testSetRequestHeaders() {
        $this->assertSame($this->driver, $this->driver->setRequestHeaders(array(
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

    /**
     * @covers ImboClient\Driver\cURL::request
     */
    public function testUrlThatRedirects() {
        $url = $this->testUrl . '?redirect=2';
        $response = unserialize($this->driver->get($url)->getBody());

        $this->assertEquals(0, $response['data']['redirect']);
    }

    /**
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage bad request
     * @expectedExceptionCode 400
     * @covers ImboClient\Driver\cURL::request
     */
    public function testRequestWhenServerRespondsWithClientError() {
        $url = $this->testUrl . '?clientError';

        try {
            $this->driver->get($url);
            $this->fail('Expected exception');
        } catch (ServerException $e) {
            $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $e->getResponse());
            throw $e;
        }
    }

    /**
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage internal server error
     * @expectedExceptionCode 500
     * @covers ImboClient\Driver\cURL::request
     */
    public function testRequestWhenServerRespondsWithServerError() {
        $url = $this->testUrl . '?serverError';

        try {
            $this->driver->get($url);
            $this->fail('Expected exception');
        } catch (ServerException $e) {
            $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $e->getResponse());
            throw $e;
        }
    }

    /**
     * @see https://github.com/imbo/imboclient-php/issues/52
     * @covers ImboClient\Driver\cURL::setRequestHeader
     * @covers ImboClient\Driver\cURL::setRequestHeaders
     */
    public function testSetSameHeaderSeveralTimes() {
        $this->driver->setRequestHeader('Foo', 'foo1');
        $this->driver->setRequestHeader('Foo', 'foo2');
        $this->driver->setRequestHeaders(array(
            'Bar' => 'bar1',
            'Bar' => 'bar2',
            'Foo' => 'foo3',
        ));

        $reflection = new ReflectionClass($this->driver);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);

        $headers = $property->getValue($this->driver);

        $this->assertArrayHasKey('Foo', $headers);
        $this->assertArrayHasKey('Bar', $headers);
        $this->assertSame('foo3', $headers['Foo']);
        $this->assertSame('bar2', $headers['Bar']);
    }

    /**
     * @covers ImboClient\Driver\cURL::__construct
     */
    public function testDriverShouldMergeCustomcURLOptionsWithDefaultOptionsWhenSpecified() {
        $driver = new cURL(array(), array(
            CURLOPT_TIMEOUT => 666,
            CURLOPT_CONNECTTIMEOUT => 333,
        ));

        $property = new ReflectionProperty('ImboClient\Driver\cURL', 'curlOptions');
        $property->setAccessible(true);

        $options = $property->getValue($driver);

        $this->assertSame(666, $options[CURLOPT_TIMEOUT]);
        $this->assertSame(333, $options[CURLOPT_CONNECTTIMEOUT]);
    }

    /**
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage Empty body
     * @expectedExceptionCode 500
     * @covers ImboClient\Driver\cURL::request
     */
    public function testDriverMustSetDefaultErrorMessageWhenResponseBodyIsEmpty() {
        $url = $this->testUrl . '?serverError&emptyBody';

        $this->driver->get($url);
    }
}
