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
 * Resource group URL
 *
 * @package Client\Urls
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class ResourceGroupUrl extends Url {
    /**
     * Get the group part of a URL
     *
     * @return string|null
     */
    public function getGroup() {
        if (preg_match('#/groups/(?<group>[^./]+)#', $this->getPath(), $match)) {
            return $match['group'];
        }

        return null;
    }
}
