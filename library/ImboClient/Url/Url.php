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
 * Abstract imbo URL for other implementations to extend
 *
 * @package ImboClient\Urls
 * @author Christer Edvartsen <cogo@starzinger.net>
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
     * @var AccessTokenInterface
     */
    private $accessToken;

    /**
     * Query params for the URL
     *
     * @var array
     */
    private $queryParams;

    /**
     * Class constructor
     *
     * @param string $baseUrl The base URL to use
     * @param string $publicKey The public key to use
     * @param string $privateKey The private key to use
     */
    public function __construct($baseUrl, $publicKey = null, $privateKey = null) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl() {
        $url = $this->getResourceUrl();

        $queryString = $this->getQueryString();

        if (!empty($this->queryParams)) {
            $url .= '?' . $queryString;
        }

        if (empty($this->publicKey) || empty($this->privateKey)) {
            return $url;
        }

        $token = $this->getAccessToken()->generateToken($url, $this->privateKey);

        return $url . (empty($this->queryParams) ? '?' : '&') . 'accessToken=' . $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlEncoded() {
        $url = $this->getUrl();

        $parts = parse_url($url);
        $parts['query'] = htmlspecialchars($parts['query']);
        $parts['query'] = str_replace('[]', '%5B%5D', $parts['query']);

        return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '?' . $parts['query'];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() {
        return $this->getUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, array $args) {
        if (!count($args)) {
            return $this;
        }

        return $this->addQueryParam($method, $args[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function addQueryParam($key, $value) {
        $this->queryParams[] = $key . '=' . urlencode($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function reset() {
        $this->queryParams = array();

        return $this;
    }

    /**
     * Get an instance of the access token
     *
     * If no instance have been provided prior to calling this method, this method must instantiate
     * the ImboClient\Url\AccessToken class and return that instance.
     *
     * @return AccessTokenInterface
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
     * @param AccessTokenInterface $accessToken An instance of the access token
     * @return UrlInterface
     */
    public function setAccessToken(AccessTokenInterface $accessToken) {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Return the query string
     *
     * @return string
     */
    private function getQueryString() {
        if (empty($this->queryParams)) {
            return '';
        }

        return implode('&', $this->queryParams);
    }

    /**
     * Get the raw URL (with no access token appended)
     *
     * @return string
     */
    abstract protected function getResourceUrl();
}
