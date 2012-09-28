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

namespace ImboClient\Url;

/**
 * Base interface for imbo URL's
 *
 * @package ImboClient\Interfaces
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
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
