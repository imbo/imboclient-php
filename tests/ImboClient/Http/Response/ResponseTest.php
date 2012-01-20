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
 * @package ImboClient
 * @subpackage Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient\Http\Response;

/**
 * @package ImboClient
 * @subpackage Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
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
     */
    public function testSetGetHeaders() {
        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');

        $this->assertSame($this->response, $this->response->setHeaders($headers));
        $this->assertSame($headers, $this->response->getHeaders());
    }

    /**
     * Test the set and get methods for the body attribute
     */
    public function testSetGetBody() {
        $body = 'Content';

        $this->assertSame($this->response, $this->response->setBody($body));
        $this->assertSame($body, $this->response->getBody());
    }

    /**
     * Test the set and get methods for the statusCode attribute
     */
    public function testSetGetStatusCode() {
        $code = 404;

        $this->assertSame($this->response, $this->response->setStatusCode($code));
        $this->assertSame($code, $this->response->getStatusCode());
    }

    /**
     * Test the isSuccess method
     */
    public function testIsSuccess() {
        $this->response->setStatusCode(200);
        $this->assertTrue($this->response->isSuccess());
        $this->response->setStatusCode(404);
        $this->assertFalse($this->response->isSuccess());
    }

    /**
     * Test the magic __toString method
     */
    public function testMagicToStringMethod() {
        $body = 'Body content';
        $this->response->setBody($body);
        $this->assertSame($body, (string) $this->response);
    }

    public function testGetImageIdentifierWhenHeaderExists() {
        $imageIdentifier = md5(microtime());
        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');
        $headers->expects($this->once())->method('get')->with('x-imbo-imageidentifier')->will($this->returnValue($imageIdentifier));

        $this->response->setHeaders($headers);

        $this->assertSame($imageIdentifier, $this->response->getImageIdentifier());
    }

    public function testGetImageIdentifierWhenHeaderDoesNotExist() {
        $headers = $this->getMock('ImboClient\Http\HeaderContainerInterface');
        $this->response->setHeaders($headers);
        $this->assertNull($this->response->getImageIdentifier());
    }

    public function testAsArray() {
        $this->assertSame($this->response, $this->response->setBody(json_encode(array('foo' => 'bar'))));
        $this->assertInternalType('array', $this->response->asArray());
    }

    public function testAsObject() {
        $this->assertSame($this->response, $this->response->setBody(json_encode(array('foo' => 'bar'))));
        $this->assertInstanceOf('stdClass', $this->response->asObject());
    }
}
