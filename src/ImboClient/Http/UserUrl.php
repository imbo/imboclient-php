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

/**
 * User URL
 *
 * @package Client\Urls
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class UserUrl extends Url {
    /**
     * Get the public key part of a URL
     *
     * @return string|null
     */
    public function getPublicKey() {
        if (preg_match('#/users/(?<publicKey>[^./]+)#', $this->getPath(), $match)) {
            return $match['publicKey'];
        }

        return null;
    }
}
