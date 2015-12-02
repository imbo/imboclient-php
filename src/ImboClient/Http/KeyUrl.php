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
 * Key URL
 *
 * @package Client\Urls
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class KeyUrl extends Url {
    /**
     * Get the key part of a URL
     *
     * @return string|null
     */
    public function getKey() {
        if (preg_match('#/keys/(?<key>[^./]+)#', $this->getPath(), $match)) {
            return $match['key'];
        }

        return null;
    }
}
