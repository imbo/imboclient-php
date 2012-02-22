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
 * @subpackage Http\Response
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient\Http\Response;

use ImboClient\Http\HeaderContainerInterface;

/**
 * Client response interface
 *
 * @package Interfaces
 * @subpackage Http\Response
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */
interface ResponseInterface {
    /**#@+
     * Internal error codes sent from the imbo server
     *
     * @var int
     */
    const ERR_UNSPECIFIED = 0;

    // Auth errors
    const AUTH_UNKNOWN_PUBLIC_KEY = 100;
    const AUTH_MISSING_PARAM      = 101;
    const AUTH_INVALID_TIMESTAMP  = 102;
    const AUTH_SIGNATURE_MISMATCH = 103;

    // Image resource errors
    const IMAGE_ALREADY_EXISTS       = 200;
    const IMAGE_NO_IMAGE_ATTACHED    = 201;
    const IMAGE_HASH_MISMATCH        = 202;
    const IMAGE_UNSUPPORTED_MIMETYPE = 203;
    /**#@-*/

    /**
     * Get the headers
     *
     * @return ImboClient\Http\HeaderContainerInterface
     */
    function getHeaders();

    /**
     * Set the headers
     *
     * @param ImboClient\Http\HeaderContainerInterface $headers The headers to set
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function setHeaders(HeaderContainerInterface $headers);

    /**
     * Get the response body
     *
     * @return string
     */
    function getBody();

    /**
     * Set the body contents
     *
     * @param string $body The string to set
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function setBody($body);

    /**
     * Get the status code
     *
     * @return int
     */
    function getStatusCode();

    /**
     * Set the status code
     *
     * @param int $code The HTTP status code to set
     * @return ImboClient\Http\Response\ResponseInterface
     */
    function setStatusCode($code);

    /**
     * Get the optional imbo error code from the body
     *
     * @return null|int
     */
    function getImboErrorCode();

    /**
     * Wether or not the response is a success (in the 2xx range)
     *
     * @return boolean
     */
    function isSuccess();

    /**
     * Returns the image identifier associated with the response
     *
     * If the response does not contain any image identitifer (for instance if the reguest made was
     * against the metadat resource) NULL will be returned.
     *
     * @return string|null
     */
    function getImageIdentifier();

    /**
     * Return the body as an array
     *
     * @return array
     */
    function asArray();

    /**
     * Return the body as an object
     *
     * @return stdClass
     */
    function asObject();
}
