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
 * Access token implementation
 *
 * @package Urls\Access token
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class AccessToken implements AccessTokenInterface {
    /**
     * {@inheritdoc}
     */
    public function generateToken($url, $key) {
        return hash_hmac('sha256', $url, $key);
    }
}
