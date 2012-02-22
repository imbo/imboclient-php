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
 * @package Http\Response
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient\Http\Response;

use ImboClient\Http\HeaderContainerInterface;
use ImboClient\Http\HeaderContainer;

/**
 * Client response
 *
 * @package Http\Response
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */
class Response implements ResponseInterface {
    /**
     * Response headers
     *
     * @var ImboClient\Http\HeaderContainerInterface
     */
    private $headers;

    /**
     * Response body
     *
     * @var string
     */
    private $body;

    /**
     * HTTP status code
     *
     * @var int
     */
    private $statusCode;

    /**
     * @see ImboClient\Http\Response\ResponseInterface::getHeaders()
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::setHeaders()
     */
    public function setHeaders(HeaderContainerInterface $headers) {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::getBody()
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::setBody()
     */
    public function setBody($body) {
        $this->body = $body;

        return $this;
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::getStatusCode()
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::setStatusCode()
     */
    public function setStatusCode($code) {
        $this->statusCode = (int) $code;

        return $this;
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::getImboErrorCode()
     */
    public function getImboErrorCode() {
        if ($this->body === null) {
            return null;
        }

        $body = json_decode($this->body, true);

        if (empty($body['error'])) {
            return null;
        }

        return empty($body['error']['imboErrorCode']) ? null : (int) $body['error']['imboErrorCode'];
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::isSuccess()
     */
    public function isSuccess() {
        $code = $this->getStatusCode();

        return ($code < 300 && $code >= 200);
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::getImageIdentifier()
     */
    public function getImageIdentifier() {
        return $this->getHeaders()->get('x-imbo-imageidentifier');
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::asArray()
     */
    public function asArray() {
        return json_decode($this->getBody(), true);
    }

    /**
     * @see ImboClient\Http\Response\ResponseInterface::asObject()
     */
    public function asObject() {
        return json_decode($this->getBody());
    }

    /**
     * Magic to string method
     *
     * This magic method returns the body
     *
     * @return string
     */
    public function __toString() {
        return $this->getBody();
    }
}
