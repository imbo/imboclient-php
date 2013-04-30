<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Http\Response;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
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
     * The response class must be able to set and get a status code
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
     * @dataProvider getCodesForIsSuccess
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
     * The response must tell whether the response is an "error" or not based on an HTTP status
     * code
     *
     * @dataProvider getCodesForIsError
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
