<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Url;

/**
 * Base interface for imbo URL's
 *
 * @package Urls
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface UrlInterface {
    /**
     * Fetch the complete URL including optional query parameters
     *
     * @return string
     */
    function getUrl();

    /**
     * Get the complete URL as an URL-encoded string
     *
     * @return string
     */
    function getUrlEncoded();

    /**
     * Resets the URL - removes all query parameters
     *
     * @return UrlInterface
     */
    function reset();

    /**
     * Method for adding query parameters to the URL
     *
     * @param string $key The name of the param. For instance "page" or "t[]"
     * @param string $value The value of the param. For instance "10" or "border:width=50,height=50"
     * @return UrlInterface
     */
    function addQueryParam($key, $value);

    /**
     * Magic call method that can be used to add simple query parameters to the URL
     *
     * This method must be implemented in such a fashion that users can call non-existing methods
     * on the URL instance that will be added as query parameters.
     *
     *     $url = new ImboClient\Url\Images('http://imbo', 'publicKey', 'privateKey');
     *     $url->page(2)->limit(10);
     *
     *     echo $url; // http://imbo/users/publicKey/images?page=2&limit=10
     *
     *
     * @param string $method The method called (will be used as query parameter name)
     * @param array $args Arguments to the method. The first argument will be used as query
     *                    parameter value
     * @return UrlInterface
     */
    function __call($method, array $args);

    /**
     * Magic to string method
     *
     * This method should proxy to getUrl()
     *
     * @return string
     */
    function __toString();
}
