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

namespace ImboClient\Http\Response;

/**
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class ResponseTest extends \PHPUnit_Framework_TestCase {
    /**
     * Response instance
     *
     * @var ImboClient\Http\Response\Response
     */
    private $response;

    /**
     * Set up method
     */
    public function setUp() {
        $this->response = new Response();
    }

    /**
     * Tear down method
     */
    public function tearDown() {
        $this->response = null;
    }

    /**
     * Test the set and get methods for the headers attribute
     *
     * @covers ImboClient\Http\Response\Response::setHeaders
     * @covers ImboClient\Http\Response\Response::getHeaders
     */
    public function testSetGetHeaders() {
        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');

        $this->assertSame($this->response, $this->response->setHeaders($headers));
        $this->assertSame($headers, $this->response->getHeaders());
    }

    /**
     * Test the set and get methods for the body attribute
     *
     * @covers ImboClient\Http\Response\Response::setBody
     * @covers ImboClient\Http\Response\Response::getBody
     */
    public function testSetGetBody() {
        $body = 'Content';

        $this->assertSame($this->response, $this->response->setBody($body));
        $this->assertSame($body, $this->response->getBody());
    }

    /**
     * Test the set and get methods for the statusCode attribute
     *
     * @covers ImboClient\Http\Response\Response::setStatusCode
     * @covers ImboClient\Http\Response\Response::getStatusCode
     */
    public function testSetGetStatusCode() {
        $code = 404;

        $this->assertSame($this->response, $this->response->setStatusCode($code));
        $this->assertSame($code, $this->response->getStatusCode());
    }

    public function getCodesForIsSuccess() {
        return array(
            array(200, true),
            array(300, false),
            array(400, false),
            array(500, false),
        );
    }

    /**
     * @dataProvider getCodesforIsSuccess
     * @covers ImboClient\Http\Response\Response::isSuccess
     */
    public function testIsSuccess($code, $success) {
        $this->response->setStatusCode($code);
        $this->assertSame($success, $this->response->isSuccess());
    }

    public function getCodesForIsError() {
        return array(
            array(200, false),
            array(300, false),
            array(400, true),
            array(500, true),
        );
    }

    /**
     * @dataProvider getCodesforIsError
     * @covers ImboClient\Http\Response\Response::isError
     */
    public function testIsError($code, $error) {
        $this->response->setStatusCode($code);
        $this->assertSame($error, $this->response->isError());
    }

    /**
     * Test the magic __toString method
     *
     * @covers ImboClient\Http\Response\Response::__toString
     */
    public function testMagicToStringMethod() {
        $body = 'Body content';
        $this->response->setBody($body);
        $this->assertSame($body, (string) $this->response);
    }

    /**
     * @covers ImboClient\Http\Response\Response::getImageIdentifier
     */
    public function testGetImageIdentifierWhenHeaderExists() {
        $imageIdentifier = md5(microtime());
        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');
        $headers->expects($this->once())->method('get')->with('x-imbo-imageidentifier')->will($this->returnValue($imageIdentifier));

        $this->response->setHeaders($headers);

        $this->assertSame($imageIdentifier, $this->response->getImageIdentifier());
    }

    /**
     * @covers ImboClient\Http\Response\Response::getImageIdentifier
     */
    public function testGetImageIdentifierWhenHeaderDoesNotExist() {
        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');
        $this->response->setHeaders($headers);
        $this->assertNull($this->response->getImageIdentifier());
    }

    /**
     * @covers ImboClient\Http\Response\Response::asArray
     */
    public function testAsArray() {
        $this->assertSame($this->response, $this->response->setBody(json_encode(array('foo' => 'bar'))));
        $this->assertInternalType('array', $this->response->asArray());
    }

    /**
     * @covers ImboClient\Http\Response\Response::asObject
     */
    public function testAsObject() {
        $this->assertSame($this->response, $this->response->setBody(json_encode(array('foo' => 'bar'))));
        $this->assertInstanceOf('stdClass', $this->response->asObject());
    }

    public function testGetImboErrorCodeWithNoBody() {
        $this->assertNull($this->response->getImboErrorCode());
    }

    public function testGetImboErrorCodeWhenBodyHasNoErrorElement() {
        $this->response->setBody(json_encode(array('foo' => 'bar')));
        $this->assertNull($this->response->getImboErrorCode());
    }

    public function testGetImboErrorCodeWhenErrorElementHasNoImboErrorCodeElement() {
        $this->response->setBody(json_encode(array('error' => array('code' => 400))));
        $this->assertNull($this->response->getImboErrorCode());
    }

    public function getErrorCodes() {
        return array(
            array(123, 123),
            array('123', 123),
        );
    }

    /**
     * @dataProvider getErrorCodes
     */
    public function testGetImboErrorCode($code, $expected) {
        $this->response->setBody(json_encode(array('error' => array('imboErrorCode' => $code))));
        $this->assertSame($expected, $this->response->getImboErrorCode());
    }
}
