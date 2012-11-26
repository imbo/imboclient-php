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
 * @package ImboClient\Http
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Http\Response;

use ImboClient\Http\HeaderContainerInterface,
    ImboClient\Http\HeaderContainer;

/**
 * Client response
 *
 * @package ImboClient\Http
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class Response implements ResponseInterface {
    /**
     * Response headers
     *
     * @var HeaderContainerInterface
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
     * {@inheritdoc}
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(HeaderContainerInterface $headers) {
        $this->headers = $headers;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($body) {
        $this->body = $body;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusCode($code) {
        $this->statusCode = (int) $code;

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function isSuccess() {
        $code = $this->getStatusCode();

        return ($code < 300 && $code >= 200);
    }

    /**
     * {@inheritdoc}
     */
    public function isError() {
        return $this->getStatusCode() >= 400;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageIdentifier() {
        return $this->getHeaders()->get('x-imbo-imageidentifier');
    }

    /**
     * {@inheritdoc}
     */
    public function asArray() {
        return json_decode($this->getBody(), true);
    }

    /**
     * {@inheritdoc}
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
        return $this->getBody() ?: '';
    }
}
