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
 * Access token interface
 *
 * @package Urls\Access token
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface AccessTokenInterface {
    /**
     * Generate an access token for a given URL using a key
     *
     * @param string $url The URL to generate the token for
     * @param string $key The key to use when generating the token
     * @return string Returns an access token for a URL. Given the same URL and key combo this
     *                method returns the same token every time.
     */
    function generateToken($url, $key);
}
