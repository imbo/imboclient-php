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
 * @package Url
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Url;

/**
 * Abstract imbo URL for other implementations to extend
 *
 * @package Url
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
abstract class Url implements UrlInterface {
    /**
     * Base URL
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Public key
     *
     * @var string
     */
    protected $publicKey;

    /**
     * Private key
     *
     * @var string
     */
    private $privateKey;

    /**
     * Access token generator
     *
     * @var ImboClient\Url\AccessTokenInterface
     */
    private $accessToken;

    /**
     * Custom query params that can be set via __call
     *
     * @var array
     */
    private $customParams;

    /**
     * Class constructor
     *
     * @param string $baseUrl The base URL to use
     * @param string $publicKey The public key to use
     * @param string $privateKey The private key to use
     */
    public function __construct($baseUrl, $publicKey, $privateKey) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * @see ImboClient\Url\UrlInterface::getUrl()
     */
    public function getUrl() {
        $url = $this->getRawUrl();

        if (!empty($this->customParams)) {
            $queryString = http_build_query($this->customParams);

            $url .= (strpos($url, '?') === false ? '?' : '&') . $queryString;
        }

        $token = $this->getAccessToken()->generateToken($url, $this->privateKey);

        return $url . (strpos($url, '?') === false ? '?' : '&') . 'accessToken=' . $token;
    }

    /**
     * @see ImboClient\Url\UrlInterface::getUrlEncoded()
     */
    public function getUrlEncoded() {
        $url = $this->getUrl();

        $parts = parse_url($url);
        $parts['query'] = htmlspecialchars($parts['query']);
        $parts['query'] = str_replace('[]', '%5B%5D', $parts['query']);

        return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '?' . $parts['query'];
    }

    /**
     * @see ImboClient\Url\UrlInterface::__toString()
     */
    public function __toString() {
        return $this->getUrl();
    }

    /**
     * Magic call method that can be used to add custom query parameters
     *
     * @param string $method The method called (will be used as query parameter name)
     * @param array $args Arguments to the method. The first argument will be used as query
     *                    parameter value
     * @return ImboClient\Url\UrlInterface
     */
    public function __call($method, array $args) {
        if (count($args)) {
            $this->customParams[$method] = $args[0];
        }

        return $this;
    }

    /**
     * Get an instance of the access token
     *
     * If no instance have been provided prior to calling this method, this method must instantiate
     * the ImboClient\Url\AccessToken class and return that instance.
     *
     * @return ImboClient\Url\AccessTokenInterface
     */
    public function getAccessToken() {
        if ($this->accessToken === null) {
            $this->accessToken = new AccessToken();
        }

        return $this->accessToken;
    }

    /**
     * Set an instance of the access token
     *
     * @return ImboClient\Url\AccessTokenInterface $accessToken An instance of the access token
     * @return ImboClient\Url\UrlInterface
     */
    public function setAccessToken(AccessTokenInterface $accessToken) {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get the raw URL (with no access token appended)
     *
     * @return string
     */
    abstract protected function getRawUrl();
}
