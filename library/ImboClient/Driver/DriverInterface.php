<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
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
