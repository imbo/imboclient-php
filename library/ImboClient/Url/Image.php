<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package ImboClient\Urls
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Url;

/**
 * Image URL
 *
 * @package ImboClient\Urls
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class Image extends Url implements ImageInterface {
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
    public function border($color = '000000', $width = 1, $height = 1) {
        return $this->addQueryParam('t[]', sprintf('border:color=%s,width=%d,height=%d', $color, $width, $height));
    }

    /**
     * {@inheritdoc}
     */
    public function compress($quality = 75) {
        return $this->addQueryParam('t[]', 'compress:quality=' . (int) $quality);
    }

    /**
     * {@inheritdoc}
     */
    public function convert($type) {
        $this->imageIdentifier = substr($this->imageIdentifier, 0, 32) . '.' . $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function gif() {
        return $this->convert('gif');
    }

    /**
     * {@inheritdoc}
     */
    public function jpg() {
        return $this->convert('jpg');
    }

    /**
     * {@inheritdoc}
     */
    public function png() {
        return $this->convert('png');
    }

    /**
     * {@inheritdoc}
     */
    public function crop($x, $y, $width, $height) {
        return $this->addQueryParam('t[]', sprintf('crop:x=%d,y=%d,width=%d,height=%d', $x, $y, $width, $height));
    }

    /**
     * {@inheritdoc}
     */
    public function flipHorizontally() {
        return $this->addQueryParam('t[]', 'flipHorizontally');
    }

    /**
     * {@inheritdoc}
     */
    public function flipVertically() {
        return $this->addQueryParam('t[]', 'flipVertically');
    }

    /**
     * {@inheritdoc}
     */
    public function resize($width = null, $height = null) {
        $params = array();

        if ($width) {
            $params[] = 'width=' . (int) $width;
        }

        if ($height) {
            $params[] = 'height=' . (int) $height;
        }

        return $this->addQueryParam('t[]', 'resize:' . implode(',', $params));
    }

    /**
     * {@inheritdoc}
     */
    public function maxSize($maxWidth = null, $maxHeight = null) {
        $params = array();

        if ($maxWidth) {
            $params[] = 'width=' . (int) $maxWidth;
        }

        if ($maxHeight) {
            $params[] = 'height=' . (int) $maxHeight;
        }

        return $this->addQueryParam('t[]', 'maxSize:' . implode(',', $params));
    }

    /**
     * {@inheritdoc}
     */
    public function rotate($angle, $bg = '000000') {
        return $this->addQueryParam('t[]', sprintf('rotate:angle=%d,bg=%s', $angle, $bg));
    }

    /**
     * {@inheritdoc}
     */
    public function thumbnail($width = 50, $height = 50, $fit = 'outbound') {
        return $this->addQueryParam('t[]', sprintf('thumbnail:width=%d,height=%s,fit=%s', $width, $height, $fit));
    }

    /**
     * {@inheritdoc}
     */
    public function canvas($width, $height, $mode = null, $x = null, $y = null, $bg = null) {
        $params = array(
            'width=' . (int) $width,
            'height=' . (int) $height,
        );

        if ($mode) {
            $params[] = 'mode=' . $mode;
        }

        if ($x) {
            $params[] = 'x=' . (int) $x;
        }

        if ($y) {
            $params[] = 'y=' . (int) $y;
        }

        if ($bg) {
            $params[] = 'bg=' . $bg;
        }

        return $this->addQueryParam('t[]', 'canvas:' . implode(',', $params));
    }

    /**
     * {@inheritdoc}
     */
    public function transpose() {
        return $this->addQueryParam('t[]', 'transpose');
    }

    /**
     * {@inheritdoc}
     */
    public function transverse() {
        return $this->addQueryParam('t[]', 'transverse');
    }

    /**
     * {@inheritdoc}
     */
    public function desaturate() {
        return $this->addQueryParam('t[]', 'desaturate');
    }

    /**
     * {@inheritdoc}
     */
    public function reset() {
        parent::reset();

        $this->imageIdentifier = substr($this->imageIdentifier, 0, 32);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResourceUrl() {
        return sprintf(
            '%s/users/%s/images/%s',
            $this->baseUrl,
            $this->publicKey,
            $this->imageIdentifier
        );
    }
}
