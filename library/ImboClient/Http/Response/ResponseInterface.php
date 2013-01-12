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

use ImboClient\Http\HeaderContainerInterface;

/**
 * Client response interface
 *
 * @package ImboClient\Interfaces
 * @author Christer Edvartsen <cogo@starzinger.net>
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
    const IMAGE_BROKEN_IMAGE         = 204;
    /**#@-*/

    /**
     * Get the headers
     *
     * @return HeaderContainerInterface
     */
    function getHeaders();

    /**
     * Set the headers
     *
     * @param HeaderContainerInterface $headers The headers to set
     * @return ResponseInterface
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
     * @return ResponseInterface
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
     * @return ResponseInterface
     */
    function setStatusCode($code);

    /**
     * Get the optional imbo error code from the body
     *
     * @return null|int
     */
    function getImboErrorCode();

    /**
     * Whether or not the response is a success (in the 2xx range)
     *
     * @return boolean
     */
    function isSuccess();

    /**
     * Whether or not the response is an error (> 4xx range)
     *
     * @return boolean
     */
    function isError();

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
