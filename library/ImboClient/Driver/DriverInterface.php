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
 * @package Interfaces
 * @subpackage Driver
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Driver;

/**
 * Client driver interface
 *
 * This is an interface for different client drivers.
 *
 * @package Interfaces
 * @subpackage Driver
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
     * @param array $metadata The metadata to POST. This array will be json_encoded and sent to the
     *                        server as $_POST['metadata']
     * @return ImboClient\Http\Response\ResponseInterface
     * @throws RuntimeException
     */
    function post($url, array $metadata = null);

    /**
     * PUT a file to an URL
     *
     * @param string $url The URL to PUT to
     * @param string $filePath Path to the local file
     * @return ImboClient\Http\Response\ResponseInterface
     * @throws RuntimeException
     */
    function put($url, $filePath);

    /**
     * PUT in-memory data to an URL
     *
     * @param string $url The URL to PUT to
     * @param string $data The data to PUT
     * @return ImboClient\Http\Response\ResponseInterface
     * @throws RuntimeException
     */
    function putData($url, $data);

    /**
     * Perform a GET to $url
     *
     * @param string $url The URL to GET
     * @return ImboClient\Http\Response\ResponseInterface
     * @throws RuntimeException
     */
    function get($url);

    /**
     * Perform a HEAD to $url
     *
     * @param string $url The URL to HEAD
     * @return ImboClient\Http\Response\ResponseInterface
     * @throws RuntimeException
     */
    function head($url);

    /**
     * Perform a DELETE request to $url
     *
     * @param string $url The URL to DELETE
     * @return ImboClient\Http\Response\ResponseInterface
     * @throws RuntimeException
     */
    function delete($url);

    /**
     * Add a request header
     *
     * @param string $key The header key
     * @param string $value The value to send
     * @return ImboClient\Driver\DriverInterface
     */
    function addRequestHeader($key, $value);
}
