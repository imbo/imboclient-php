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
 * @package Client
 * @subpackage ImageUrl
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\ImageUrl;

/**
 * Image url implementation
 *
 * @package Client
 * @subpackage ImageUrl
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class ImageUrl implements ImageUrlInterface {
    /**
     * Baseurl
     *
     * @var string
     */
    private $baseUrl;

    /**
     * The public key
     *
     * @var string
     */
    private $publicKey;

    /**
     * The private key
     *
     * @var string
     */
    private $privateKey;

    /**
     * The image identifier
     *
     * @var string
     */
    private $imageIdentifier;

    /**
     * Query data
     *
     * @var string[]
     */
    private $data;

    /**
     * Class constructor
     *
     * @param string $baseUrl The baseurl to the server
     * @param string $publicKey The public key to use
     * @param string $privateKey The private key to use
     * @param string $imageIdentifier The image identifier
     */
    public function __construct($baseUrl, $publicKey, $privateKey, $imageIdentifier) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->imageIdentifier = $imageIdentifier;
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::border()
     */
    public function border($color = '000000', $width = 1, $height = 1) {
        return $this->append(sprintf('border:color=%s,width=%d,height=%d', $color, $width, $height));
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::compress()
     */
    public function compress($quality = 75) {
        return $this->append('compress:quality=' . (int) $quality);
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::convert()
     */
    public function convert($type) {
        $this->imageIdentifier = substr($this->imageIdentifier, 0, 32) . '.' . $type;

        return $this;
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::gif()
     */
    public function gif() {
        return $this->convert('gif');
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::jpg()
     */
    public function jpg() {
        return $this->convert('jpg');
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::png()
     */
    public function png() {
        return $this->convert('png');
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::crop()
     */
    public function crop($x, $y, $width, $height) {
        return $this->append(sprintf('crop:x=%d,y=%d,width=%d,height=%d', $x, $y, $width, $height));
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::flipHorizontally()
     */
    public function flipHorizontally() {
        return $this->append('flipHorizontally');
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::flipVertically()
     */
    public function flipVertically() {
        return $this->append('flipVertically');
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::resize()
     */
    public function resize($width = null, $height = null) {
        $params = array();

        if ($width) {
            $params[] = 'width=' . (int) $width;
        }

        if ($height) {
            $params[] = 'height=' . (int) $height;
        }

        return $this->append('resize:' . implode(',', $params));
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::maxSize()
     */
    public function maxSize($maxWidth = null, $maxHeight = null) {
        $params = array();

        if ($maxWidth) {
            $params[] = 'width=' . (int) $maxWidth;
        }

        if ($maxHeight) {
            $params[] = 'height=' . (int) $maxHeight;
        }

        return $this->append('maxSize:' . implode(',', $params));
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::rotate()
     */
    public function rotate($angle, $bg = '000000') {
        return $this->append(sprintf('rotate:angle=%d,bg=%s', $angle, $bg));
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::thumbnail()
     */
    public function thumbnail($width = 50, $height = 50, $fit = 'outbound') {
        return $this->append(sprintf('thumbnail:width=%d,height=%s,fit=%s', $width, $height, $fit));
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::canvas()
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

        return $this->append('canvas:' . implode(',', $params));
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::reset()
     */
    public function reset() {
        $this->data = array();
        $this->imageIdentifier = substr($this->imageIdentifier, 0, 32);

        return $this;
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::getUrlEncoded()
     */
    public function getUrlEncoded() {
        $queryString = htmlspecialchars($this->getQueryString());
        $queryString = str_replace('[]', '%5B%5D', $queryString);

        return $this->getImageUrl() . ($queryString ? '?' . $queryString : '');
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::getUrl()
     */
    public function getUrl() {
        $queryString = $this->getQueryString();

        return $this->getImageUrl() . ($queryString ? '?' . $queryString : '');
    }

    /**
     * @see ImboClient\ImageUrl\ImageUrlInterface::__toString()
     */
    public function __toString() {
        return $this->getUrl();
    }

    /**
     * Append a string to the query
     *
     * @param string $part The string to append
     * @return ImboClient\ImageUrl\ImageUrlInterface
     */
    private function append($part) {
        $this->data[] = $part;

        return $this;
    }

    /**
     * Return the URL for the image, without transformations
     *
     * @return string
     */
    private function getImageUrl() {
        return $this->baseUrl . '/users/' . $this->publicKey . '/images/' . $this->imageIdentifier;
    }

    /**
     * Return the query string
     *
     * @return string
     */
    private function getQueryString() {
        if (empty($this->data) && !isset($this->imageIdentifier[32])) {
            // We don't have any transformations added or a custom extension
            return '';
        }

        // Initialize data for the transformation key hash
        $data = $this->publicKey . '|' . $this->imageIdentifier;
        $queryString = '';

        if (!empty($this->data)) {
            // We have some transformations. Generate a transformation key that will be sent to the
            // server so the server can verify if the transformations are valid or not.
            $queryString = array_reduce($this->data, function($query, $element) {
                return $query . 't[]=' . $element . '&';
            }, $queryString);

            $queryString = rtrim($queryString, '&');

            $data .= '|' . $queryString;
        }

        // Prepare data for the hash
        $transformationKey = hash_hmac('md5', $data, $this->privateKey);

        if (empty($queryString)) {
            // No query string. Return only the transformation key
            return 'tk=' . $transformationKey;
        }

        // Return the query string with the transformation key appended
        return $queryString . '&tk=' . $transformationKey;
    }
}
