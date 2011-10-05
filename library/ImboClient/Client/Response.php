<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
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
 * @subpackage Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient\Client;

/**
 * Client response
 *
 * @package ImboClient
 * @subpackage Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */
class Response {
    /**
     * Response headers
     *
     * @var array
     */
    private $headers = array();

    /**
     * Response body
     *
     * @var string
     */
    private $body = null;

    /**
     * HTTP status code
     *
     * @var int
     */
    private $statusCode;

    /**
     * Get the headers
     *
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Set the headers
     *
     * @param array $headers The headers to set
     * @return ImboClient\Client\Response
     */
    public function setHeaders(array $headers) {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get the response body
     *
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Set the body contents
     *
     * @param string $body The string to set
     * @return ImboClient\Client\Response
     */
    public function setBody($body) {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the status code
     *
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Set the status code
     *
     * @param int $code The HTTP status code to set
     * @return ImboClient\Client\Response
     */
    public function setStatusCode($code) {
        $this->statusCode = (int) $code;

        return $this;
    }

    /**
     * Wether or not the response is a success (in the 2xx range)
     *
     * @return boolean
     */
    public function isSuccess() {
        $code = $this->getStatusCode();

        return ($code < 300 && $code >= 200);
    }

    /**
     * Returns the image identifier associated with the response
     *
     * @return string|boolean
     */
    public function getImageIdentifier() {
        return isset($this->headers['X-Imbo-Image-Identifier']) ? $this->headers['X-Imbo-Image-Identifier'] : false;
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

    /**
     * Create a new instance of this object (based on the $content)
     *
     * @param string $content Content from a curl_exec() call (including the headers)
     * @param int $responseCode The responsecode. If not set the factory will try to figure out the
     *                          code based on the header part of the $content.
     * @return ImboClient\Client\Response
     */
    static public function factory($content, $responseCode = null) {
        // Remove \r from the string
        $content = str_replace("\r", '', $content);

        // Separate headers and body
        list($headers, $body) = explode("\n\n", $content, 2);

        // Create an array of the headers
        $headers = explode("\n", $headers);

        // Remove the first element
        $protocol = array_shift($headers);

        // Seperate into an associative array
        $associativeHeaders = array();
        foreach ($headers as $header) {
            list($key, $value) = explode(': ', $header, 2);
            $associativeHeaders[$key] = $value;
        };

        if ($responseCode === null) {
            $responseCode = 200;

            if (preg_match('|^HTTP/\d.\d ([\d]{3}) .*$|', $protocol, $matches)) {
                $responseCode = (int) $matches[1];
            }
        }

        // Build the response object
        $response = new static();
        $response->setBody($body)
                 ->setStatusCode($responseCode)
                 ->setHeaders($associativeHeaders);

        return $response;
    }

    /**
     * Return the body as an array
     *
     * @return array
     */
    public function asArray() {
        return json_decode($this->getBody(), true);
    }

    /**
     * Return the body as an object
     *
     * @return stdClass
     */
    public function asObject() {
        return json_decode($this->getBody());
    }
}
