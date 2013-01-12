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

use ImboClient\Http\HeaderContainerInterface,
    ImboClient\Http\HeaderContainer;

/**
 * Client response
 *
 * @package ImboClient\Http
 * @author Christer Edvartsen <cogo@starzinger.net>
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
