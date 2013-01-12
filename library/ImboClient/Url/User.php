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
 * User URL
 *
 * @package Urls\User
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class User extends Url implements UrlInterface {
    /**
     * {@inheritdoc}
     */
    protected function getResourceUrl() {
        return sprintf(
            '%s/users/%s.json',
            $this->baseUrl,
            $this->publicKey
        );
    }
}
