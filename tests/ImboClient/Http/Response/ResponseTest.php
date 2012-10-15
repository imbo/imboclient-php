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

namespace ImboClient\Http\Response;

/**
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class ResponseTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ImboClient\Http\Response\Response
     */
    private $response;

    /**
     * Set up the response instance
     */
    public function setUp() {
        $this->response = new Response();
    }

    /**
     * Tear down the response instance
     */
    public function tearDown() {
        $this->response = null;
    }

    /**
     * The response class must be able to set and get a header container
     *
     * @covers ImboClient\Http\Response\Response::setHeaders
     * @covers ImboClient\Http\Response\Response::getHeaders
     */
    public function testCanSetAndGetAHeaderContainer() {
        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');

        $this->assertSame($this->response, $this->response->setHeaders($headers));
        $this->assertSame($headers, $this->response->getHeaders());
    }

    /**
     * The response class must be able to set and get a body
     *
     * @covers ImboClient\Http\Response\Response::setBody
     * @covers ImboClient\Http\Response\Response::getBody
     */
    public function testCanSetAndGetABody() {
        $body = 'Content';

        $this->assertSame($this->response, $this->response->setBody($body));
        $this->assertSame($body, $this->response->getBody());
    }

    /**
     * The response class must be able to set and get a body
     *
     * @covers ImboClient\Http\Response\Response::setStatusCode
     * @covers ImboClient\Http\Response\Response::getStatusCode
     */
    public function testCanSetAndGetAStatusCode() {
        $code = 404;

        $this->assertSame($this->response, $this->response->setStatusCode($code));
        $this->assertSame($code, $this->response->getStatusCode());
    }

    /**
     * Get different HTTP status codes and the value for "success"
     *
     * @return array[]
     */
    public function getCodesForIsSuccess() {
        return array(
            array(200, true),
            array(300, false),
            array(400, false),
            array(500, false),
        );
    }

    /**
     * The response must tell whether the response is a "success" or not based on an HTTP status
     * code
     *
     * @dataProvider getCodesforIsSuccess
     * @covers ImboClient\Http\Response\Response::isSuccess
     * @covers ImboClient\Http\Response\Response::getStatusCode
     */
    public function testCanTellWhetherOrNotTheResponseIsASuccessBasedOnAnHttpStatusCode($code, $success) {
        $this->response->setStatusCode($code);
        $this->assertSame($success, $this->response->isSuccess());
    }

    /**
     * Get different HTTP status codes and the value for "error"
     *
     * @return array[]
     */
    public function getCodesForIsError() {
        return array(
            array(200, false),
            array(300, false),
            array(400, true),
            array(500, true),
        );
    }

    /**
     * The response must tell whether the response is a "success" or not based on an HTTP status
     * code
     *
     * @dataProvider getCodesforIsError
     * @covers ImboClient\Http\Response\Response::isError
     * @covers ImboClient\Http\Response\Response::getStatusCode
     */
    public function testCanTellWhetherOrNotTheResponseIsAnErrorBasedOnAnHttpStatusCode($code, $error) {
        $this->response->setStatusCode($code);
        $this->assertSame($error, $this->response->isError());
    }

    /**
     * The response must return the body in a string context
     *
     * @covers ImboClient\Http\Response\Response::__toString
     * @covers ImboClient\Http\Response\Response::getBody
     */
    public function testReturnsTheBodyWhenUsedInAStringContext() {
        $this->assertEmpty((string) $this->response);
        $body = 'Body content';
        $this->response->setBody($body);
        $this->assertSame($body, (string) $this->response);
    }

    /**
     * The response must be able to return the image identifier if it exists in the header
     * collection
     *
     * @covers ImboClient\Http\Response\Response::getImageIdentifier
     */
    public function testCanFetchAnImageIdentifierIfItExistsAsAHeader() {
        $imageIdentifier = '57cc615a80f6c623a138846cf7509028';

        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');
        $headers->expects($this->at(0))
                ->method('get')
                ->with('x-imbo-imageidentifier')
                ->will($this->returnValue(null));
        $headers->expects($this->at(1))
                ->method('get')
                ->with('x-imbo-imageidentifier')
                ->will($this->returnValue($imageIdentifier));

        $this->response->setHeaders($headers);

        $this->assertNull($this->response->getImageIdentifier());
        $this->assertSame($imageIdentifier, $this->response->getImageIdentifier());
    }

    /**
     * Can return the JSON encoded body as an array
     *
     * @covers ImboClient\Http\Response\Response::asArray
     */
    public function testCanReturnAJsonEncodedBodyAsAnArray() {
        $this->assertSame(
            $this->response,
            $this->response->setBody(json_encode(array('foo' => 'bar')))
        );

        $this->assertSame(array('foo' => 'bar'), $this->response->asArray());
    }

    /**
     * Can return the JSON encoded body as an object
     *
     * @covers ImboClient\Http\Response\Response::asObject
     */
    public function testCanReturnAJsonEncodedBodyAsAnObject() {
        $this->assertSame(
            $this->response,
            $this->response->setBody(json_encode(array('foo' => 'bar')))
        );

        $body = $this->response->asObject();
        $this->assertInstanceOf('stdClass', $body);
        $this->assertSame('bar', $body->foo);
    }

    /**
     * Return body data along with a resulting imbo error code for that body
     *
     * @return array[]
     */
    public function getBodyWithErrorCode() {
        return array(
            array(null, null),
            array(json_encode('foobar'), null),
            array(json_encode(array('foo' => 'bar')), null),
            array(json_encode(array('error' => array('code' => 400))), null),
            array(json_encode(array('error' => array('imboErrorCode' => 400))), 400),
            array(json_encode(array('error' => array('imboErrorCode' => '400'))), 400),
        );
    }

    /**
     * The response must return the imbo error code only if the response body has a correct error
     * element
     *
     * @dataProvider getBodyWithErrorCode
     * @covers ImboClient\Http\Response\Response::getImboErrorCode
     */
    public function testCanReturnAnImboErrorCodeWhenTheBodyHasAnErrorElement($body, $code) {
        $this->response->setBody($body);
        $this->assertSame($code, $this->response->getImboErrorCode());
    }
}
