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

namespace ImboClient\Driver;

use ImboClient\Exception\ServerException,
    ReflectionProperty;

/**
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class cURLTest extends \PHPUnit_Framework_TestCase {
    /**
     * The driver instance
     *
     * @var cURL
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
     *
     * @covers ImboClient\Driver\cURL::__construct
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
     * The driver must be able to POST data
     *
     * @covers ImboClient\Driver\cURL::post
     * @covers ImboClient\Driver\cURL::request
     */
    public function testCanPostDataUsingHttpPost() {
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
     * The driver must be able to put a file using HTTP PUT
     *
     * @covers ImboClient\Driver\cURL::put
     * @covers ImboClient\Driver\cURL::request
     */
    public function testCanPutAFileUsingHttpPut() {
        $response = $this->driver->put($this->testUrl, __FILE__);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $data = unserialize($response->getBody());
        $this->assertSame($data['md5'], md5_file(__FILE__));
    }

    /**
     * The driver must be able to put data using HTTP PUT
     *
     * @covers ImboClient\Driver\cURL::putData
     * @covers ImboClient\Driver\cURL::request
     */
    public function testCanPutDataUsingHttpPut() {
        $file = file_get_contents(__FILE__);
        $response = $this->driver->putData($this->testUrl, $file);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $result = unserialize($response->getBody());
        $this->assertSame('PUT', $result['method']);
        $this->assertSame($result['md5'], md5($file));
    }

    /**
     * The driver must be able to request a URL using HTTP GET
     *
     * @covers ImboClient\Driver\cURL::get
     * @covers ImboClient\Driver\cURL::request
     */
    public function testCanRequestAnUrlWithQueryParametersUsingHttpGet() {
        $url = $this->testUrl . '?foo=bar&bar=foo';
        $response = $this->driver->get($url);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $result = unserialize($response->getBody());
        $this->assertSame('GET', $result['method']);
        $this->assertSame(array('foo' => 'bar', 'bar' => 'foo'), $result['data']);
    }

    /**
     * The driver must be able to request a URL using HTTP HEAD
     *
     * @covers ImboClient\Driver\cURL::head
     * @covers ImboClient\Driver\cURL::request
     */
    public function testCanRequestAnUrlUsingHttpHead() {
        $response = $this->driver->head($this->testUrl);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $this->assertEmpty($response->getBody());
    }

    /**
     * The driver must be able to request a URL using HTTP DELETE
     *
     * @covers ImboClient\Driver\cURL::delete
     * @covers ImboClient\Driver\cURL::request
     */
    public function testCanRequestAnUrlUsingHttpDelete() {
        $response = $this->driver->delete($this->testUrl);
        $this->assertInstanceOf('ImboClient\Http\Response\ResponseInterface', $response);
        $result = unserialize($response->getBody());
        $this->assertSame('DELETE', $result['method']);
    }

    /**
     * The driver must time out if the server uses more time than what the driver accepts
     *
     * @expectedException ImboClient\Exception\RuntimeException
     * @expectedExceptionMessage An error occured. Request timed out during transfer (limit: 2s).
     * @covers ImboClient\Driver\cURL::get
     * @covers ImboClient\Driver\cURL::request
     */
    public function testTimesOutWhenTheServerTakesTooLongToRespond() {
        $url = $this->testUrl . '?sleep=3';
        $this->driver->get($url);
    }

    /**
     * The driver must be able to accept custom parameters through the constructor that will
     * override the default values
     *
     * @expectedException ImboClient\Exception\RuntimeException
     * @expectedExceptionMessage An error occured. Request timed out during transfer (limit: 1s).
     * @covers ImboClient\Driver\cURL::__construct
     * @covers ImboClient\Driver\cURL::get
     * @covers ImboClient\Driver\cURL::request
     */
    public function testAcceptsCustomParametersThroughConstructor() {
        $params = array(
            'timeout' => 1,
        );
        $driver = new cURL($params);
        $url = $this->testUrl . '?sleep=2';
        $driver->get($url);
    }

    /**
     * The driver must not include the Expect request header pr default
     *
     * @covers ImboClient\Driver\cURL::post
     * @covers ImboClient\Driver\cURL::request
     * @covers ImboClient\Driver\cURL::setRequestHeader
     */
    public function testDoesNotIncludeExpectHeaderPrDefault() {
        $postData = '{"some":"data"}';
        $url = $this->testUrl . '?headers';
        $response = $this->driver->post($url, $postData);
        $headers = unserialize($response->getBody());

        $this->assertArrayNotHasKey('HTTP_EXPECT', $headers);

        // Add a header and make the same request
        $this->assertSame($this->driver, $this->driver->setRequestHeader('Header', 'value'));

        $response = $this->driver->post($url, $postData);
        $headers = unserialize($response->getBody());

        $this->assertArrayNotHasKey('HTTP_EXPECT', $headers);
    }

    /**
     * The driver must support setting an additional request header
     *
     * @covers ImboClient\Driver\cURL::setRequestHeader
     */
    public function testCanSetAnAdditionalRequestHeader() {
        $this->assertSame($this->driver, $this->driver->setRequestHeader('Header', 'value'));
        $url = $this->testUrl . '?headers';
        $response = $this->driver->get($url);
        $headers = unserialize($response->getBody());

        $this->assertArrayHasKey('HTTP_HEADER', $headers);
        $this->assertSame('value', $headers['HTTP_HEADER']);
    }

    /**
     * The driver must support setting multiple additional request header
     *
     * @covers ImboClient\Driver\cURL::setRequestHeader
     * @covers ImboClient\Driver\cURL::setRequestHeaders
     */
    public function testCanSetMultipleAdditionalRequestHeaders() {
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
     * The driver must follow redirects
     *
     * @covers ImboClient\Driver\cURL::get
     * @covers ImboClient\Driver\cURL::request
     */
    public function testFollowsRedirects() {
        $url = $this->testUrl . '?redirect=2';
        $response = unserialize($this->driver->get($url)->getBody());

        $this->assertEquals(0, $response['data']['redirect']);
    }

    /**
     * The driver must throw an exception when the server responds with an error as well as make
     * the response available through the exception
     *
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage Bad Request
     * @expectedExceptionCode 400
     * @covers ImboClient\Driver\cURL::get
     * @covers ImboClient\Driver\cURL::request
     * @covers ImboClient\Exception\ServerException::getResponse
     */
    public function testThrowsExceptionWhenTheServerRespondsWithAClientErrorAndMakesTheResponseAvailableThroughTheException() {
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
     * The driver must throw an exception when the server responds with an error as well as make
     * the response available through the exception
     *
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage Internal Server Error
     * @expectedExceptionCode 500
     * @covers ImboClient\Driver\cURL::get
     * @covers ImboClient\Driver\cURL::request
     * @covers ImboClient\Exception\ServerException::getResponse
     */
    public function testThrowsExceptionWhenTheServerRespondsWithAServerErrorAndMakesTheResponseAvailableThroughTheException() {
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
     * The driver must not include duplicate request headers
     *
     * @link https://github.com/imbo/imboclient-php/issues/52
     * @covers ImboClient\Driver\cURL::setRequestHeader
     * @covers ImboClient\Driver\cURL::setRequestHeaders
     */
    public function testDoesNotSendDuplicateRequestHeaders() {
        $this->driver->setRequestHeader('Foo', 'foo1');
        $this->driver->setRequestHeader('Foo', 'foo2');
        $this->driver->setRequestHeaders(array(
            'Bar' => 'bar1',
            'Bar' => 'bar2',
            'Foo' => 'foo3',
        ));

        $property = new ReflectionProperty('ImboClient\Driver\cURL', 'headers');
        $property->setAccessible(true);

        $response = $this->driver->get($this->testUrl . '?headers');
        $headers = unserialize($response->getBody());

        $this->assertArrayHasKey('HTTP_FOO', $headers);
        $this->assertArrayHasKey('HTTP_BAR', $headers);
        $this->assertSame('foo3', $headers['HTTP_FOO']);
        $this->assertSame('bar2', $headers['HTTP_BAR']);
    }

    /**
     * The driver must merge custom cURL options with the default ones provided to the constructor
     *
     * @covers ImboClient\Driver\cURL::__construct
     */
    public function testAcceptsCustomCurlParametersThroughConstructor() {
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
     * The driver must set a default error message when the server responds with an error and an
     * empty body (typically a response to a HEAD request)
     *
     * @expectedException ImboClient\Exception\ServerException
     * @expectedExceptionMessage Empty body
     * @expectedExceptionCode 500
     * @covers ImboClient\Driver\cURL::get
     * @covers ImboClient\Driver\cURL::request
     */
    public function testSetsADefaultErrorMessageWhenTheServerRespondsWithAnErrorAndAnEmptyResponseBody() {
        $url = $this->testUrl . '?serverError&emptyBody';

        $this->driver->get($url);
    }
}
