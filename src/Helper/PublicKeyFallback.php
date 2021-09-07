<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Helper;

/**
 * Public key fallback helper. Used to translate between user and public keys,
 * which changed meaning in Imbo 2.0.
 *
 * @package Client\Helper
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class PublicKeyFallback {
    /**
     * For backwards-compatibility, we provide both `user` and `publicKey`.
     * In future version of ImboClient, the `publicKey` will be removed.
     *
     * @param array $info
     * @return array
     */
    public static function fallback($info) {
        $user = isset($info['user']) ? $info['user'] : null;
        $publicKey = isset($info['publicKey']) ? $info['publicKey'] : null;

        if (!$user && $publicKey) {
            $info['user'] = $publicKey;
        } else if (!$publicKey && $user) {
            $info['publicKey'] = $user;
        }

        return $info;
    }
}
