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
 * Metadata URL
 *
 * @package Urls\Metadata
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Metadata extends Url implements UrlInterface {
    /**
     * Image identifier
     *
     * @var string
     */
    private $imageIdentifier;

    /**
     * Class constructor
     *
     * {@inheritdoc}
     * @param string $imageIdentifier The image identifier to use in the URL
     */
    public function __construct($baseUrl, $publicKey, $privateKey, $imageIdentifier) {
        parent::__construct($baseUrl, $publicKey, $privateKey);

        $this->imageIdentifier = $imageIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResourceUrl() {
        return sprintf(
            '%s/users/%s/images/%s/meta.json',
            $this->baseUrl,
            $this->publicKey,
            $this->imageIdentifier
        );
    }
}
