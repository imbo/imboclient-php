<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Exception;

/**
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ServerExceptionTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ServerException
     */
    private $exception;

    /**
     * Set up the exception instance
     */
    public function setUp() {
        $this->exception = new ServerException();
    }

    /**
     * Tear down the exception instance
     */
    public function tearDown() {
        $this->exception = null;
    }

    /**
     * The server exception must be able to set and get a response instance
     *
     * @covers ImboClient\Exception\ServerException::setResponse
     * @covers ImboClient\Exception\ServerException::getResponse
     */
    public function testCanSetAndGetAResponseInstance() {
        $response = $this->getMock('ImboClient\Http\Response\ResponseInterface');

        $this->assertSame($this->exception, $this->exception->setResponse($response));
        $this->assertSame($response, $this->exception->getResponse());
    }

    /**
     * Initially the server exception instance does not have a response instance
     *
     * @covers ImboClient\Exception\ServerException::getResponse
     */
    public function testHasInitiallyNoResponseInstance() {
        $this->assertNull($this->exception->getResponse());
    }
}
