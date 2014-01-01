<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Http;

use Guzzle\Http\Url as GuzzleUrl;

/**
 * Base URL class
 *
 * @package Client\Urls
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Url extends GuzzleUrl {
    /**
     * Private key of a user
     *
     * @var string
     */
    private $privateKey;

    /**
     * Factory method
     *
     * @param string $url URL as a string
     * @param string $privateKey Optional private key
     * @return Url
     */
    public static function factory($url, $privateKey = null) {
        $url = parent::factory($url);

        if ($privateKey) {
            $url->setPrivateKey($privateKey);
        }

        return $url;
    }

    /**
     * Return the URL as a string
     *
     * @return string
     */
    public function __toString() {
        $asString = parent::__toString();

        if ($this->privateKey) {
            $accessToken = hash_hmac('sha256', $asString, $this->privateKey);

            $url = GuzzleUrl::factory($asString);
            $url->getQuery()->set('accessToken', $accessToken);

            return (string) $url;
        }

        return $asString;
    }

    /**
     * Set the private key
     *
     * @param string $privateKey The private key to use when appending access tokens to the URL's
     */
    public function setPrivateKey($privateKey) {
        $this->privateKey = $privateKey;
    }
}
