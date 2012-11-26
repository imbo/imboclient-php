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
 * @package ImboClient\Interfaces
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Driver;

use ImboClient\Http\Response\ResponseInterface,
    ImboClient\Exception\ServerException,
    ImboClient\Exception\RuntimeException;

/**
 * Client driver interface
 *
 * This is an interface for different client drivers.
 *
 * @package ImboClient\Interfaces
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
interface DriverInterface {
    /**#@+
     * HTTP methods
     *
     * @var string
     */
    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const HEAD   = 'HEAD';
    const DELETE = 'DELETE';
    /**#@-*/

    /**
     * POST some data to an URL
     *
     * @param string $url The URL to POST to
     * @param string $metadata The metadata to POST. The data must be JSON-encoded
     * @param array $headers Additional headers to send in this request as an associative array
     * @return ResponseInterface
     * @throws RuntimeException|ServerException
     */
    function post($url, $metadata, array $headers = array());

    /**
     * PUT a file to an URL
     *
     * @param string $url The URL to PUT to
     * @param string $filePath Path to the local file
     * @return ResponseInterface
     * @throws RuntimeException|ServerException
     */
    function put($url, $filePath);

    /**
     * PUT in-memory data to an URL
     *
     * @param string $url The URL to PUT to
     * @param string $data The data to PUT
     * @param array $headers Additional headers to send in this request as an associative array
     * @return ResponseInterface
     * @throws RuntimeException|ServerException
     */
    function putData($url, $data, array $headers = array());

    /**
     * Perform a GET to $url
     *
     * @param string $url The URL to GET
     * @return ResponseInterface
     * @throws RuntimeException|ServerException
     */
    function get($url);

    /**
     * Perform a HEAD to $url
     *
     * @param string $url The URL to HEAD
     * @return ResponseInterface
     * @throws RuntimeException|ServerException
     */
    function head($url);

    /**
     * Perform a DELETE request to $url
     *
     * @param string $url The URL to DELETE
     * @return ResponseInterface
     * @throws RuntimeException|ServerException
     */
    function delete($url);

    /**
     * Set a request header
     *
     * @param string $key The header key
     * @param string $value The value to send
     * @return DriverInterface
     */
    function setRequestHeader($key, $value);

    /**
     * Set one or more request headers
     *
     * @param array $headers Associative array
     * @return DriverInterface
     */
    function setRequestHeaders(array $headers);
}
