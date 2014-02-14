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

use InvalidArgumentException;

/**
 * Image URL
 *
 * @package Client\Urls
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ImageUrl extends Url {
    /**
     * Transformations
     *
     * @var string[]
     */
    private $transformations = array();

    /**
     * Image extension
     *
     * @var string
     */
    private $extension;

    /**
     * Add a transformation
     *
     * @param string $transformation A transformation
     * @return self
     */
    public function addTransformation($transformation) {
        $this->transformations[] = $transformation;

        return $this;
    }

    /**
     * Add an auto rotate transformation
     *
     * @return self
     */
    public function autoRotate() {
        return $this->addTransformation('autoRotate');
    }

    /**
     * Add a border transformation
     *
     * @param string $color Color of the border
     * @param int $width Width of the left and right sides of the border
     * @param int $height Height of the top and bottom sides of the border
     * @param string $mode The mode of the border, "inline" or "outbound"
     * @return self
     */
    public function border($color = '000000', $width = 1, $height = 1, $mode = 'outbound') {
        return $this->addTransformation(
            sprintf('border:color=%s,width=%d,height=%d,mode=%s', $color, (int) $width, (int) $height, $mode)
        );
    }

    /**
     * Add a canvas transformation
     *
     * @param int $width Width of the canvas
     * @param int $height Height of the canvas
     * @param string $mode The placement mode, "free", "center", "center-x" or "center-y"
     * @param int $x X coordinate of the placement of the upper left corner of the existing image
     * @param int $y Y coordinate of the placement of the upper left corner of the existing image
     * @param string $bg Background color of the canvas
     * @return self
     * @throws InvalidArgumentException
     */
    public function canvas($width, $height, $mode = null, $x = null, $y = null, $bg = null) {
        if (!$width || !$height) {
            throw new InvalidArgumentException('width and height must be specified');
        }

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

        return $this->addTransformation(sprintf('canvas:%s', implode(',', $params)));
    }

    /**
     * Add a compress transformation
     *
     * @param int $level A value between 0 and 100 where 100 is the best
     * @return self
     */
    public function compress($level = 75) {
        return $this->addTransformation(sprintf('compress:level=%d', (int) $level));
    }

    /**
     * Specify the image extension
     *
     * @param string $type The type to convert to, "png", "jpg" or "gif"
     * @return self
     */
    public function convert($type) {
        $this->extension = $type;

        return $this;
    }

    /**
     * Add a crop transformation
     *
     * @param int $x X coordinate of the top left corner of the crop
     * @param int $y Y coordinate of the top left corner of the crop
     * @param int $width Width of the crop
     * @param int $height Height of the crop
     * @param string $mode The crop mode. Available in Imbo >= 1.1.0.
     * @return self
     * @throws InvalidArgumentException
     */
    public function crop($x = null, $y = null, $width = null, $height = null, $mode = null) {
        if ($mode === null && ($x === null || $y === null)) {
            throw new InvalidArgumentException('x and y needs to be specified without a crop mode');
        }

        if ($mode === 'center-x' && $y === null) {
            throw new InvalidArgumentException('y needs to be specified when mode is center-x');
        }

        if ($mode === 'center-y' && $x === null) {
            throw new InvalidArgumentException('x needs to be specified when mode is center-y');
        }

        if ($width === null || $height === null) {
            throw new InvalidArgumentException('width and height needs to be specified');
        }

        $params = array(
            'width=' . (int) $width,
            'height=' . (int) $height,
        );

        if ($x) {
            $params[] = 'x=' . (int) $x;
        }

        if ($y) {
            $params[] = 'y=' . (int) $y;
        }

        if ($mode) {
            $params[] = 'mode=' . $mode;
        }

        return $this->addTransformation('crop:' . implode(',', $params));
    }

    /**
     * Add a desaturate transformation
     *
     * @return self
     */
    public function desaturate() {
        return $this->addTransformation('desaturate');
    }

    /**
     * Add a flipHorizontally transformation
     *
     * @return self
     */
    public function flipHorizontally() {
        return $this->addTransformation('flipHorizontally');
    }

    /**
     * Add a flipVertically transformation
     *
     * @return self
     */
    public function flipVertically() {
        return $this->addTransformation('flipVertically');
    }

    /**
     * Add a maxSize transformation
     *
     * @param int $maxWidth Max width of the resized image
     * @param int $maxHeight Max height of the resized image
     * @return self
     * @throws InvalidArgumentException
     */
    public function maxSize($maxWidth = null, $maxHeight = null) {
        $params = array();

        if ($maxWidth) {
            $params[] = 'width=' . (int) $maxWidth;
        }

        if ($maxHeight) {
            $params[] = 'height=' . (int) $maxHeight;
        }

        if (!$params) {
            throw new InvalidArgumentException('width and/or height must be specified');
        }

        return $this->addTransformation(sprintf('maxSize:%s', implode(',', $params)));
    }

    /**
     * Add a progressive transformation
     *
     * @return self
     */
    public function progressive() {
        return $this->addTransformation('progressive');
    }

    /**
     * Add a resize transformation
     *
     * @param int $width Width of the resized image
     * @param int $height Height of the resized image
     * @return self
     * @throws InvalidArgumentException
     */
    public function resize($width = null, $height = null) {
        $params = array();

        if ($width) {
            $params[] = 'width=' . (int) $width;
        }

        if ($height) {
            $params[] = 'height=' . (int) $height;
        }

        if (!$params) {
            throw new InvalidArgumentException('width and/or height must be specified');
        }

        return $this->addTransformation(sprintf('resize:%s', implode(',', $params)));
    }

    /**
     * Add a rotate transformation
     *
     * @param float $angle The angle to rotate
     * @param string $bg Background color of the rotated image
     * @return self
     * @throws InvalidArgumentException
     */
    public function rotate($angle, $bg = '000000') {
        if (!$angle) {
            throw new InvalidArgumentException('angle must be specified');
        }

        return $this->addTransformation(sprintf('rotate:angle=%d,bg=%s', (int) $angle, $bg));
    }

    /**
     * Add a sepia transformation
     *
     * @param int $threshold Measure of the extent of sepia toning (ranges from 0 to QuantumRange)
     * @return self
     */
    public function sepia($threshold = 80) {
        return $this->addTransformation(sprintf('sepia:threshold=%d', (int) $threshold));
    }

    /**
     * Add a strip transformation
     *
     * @return self
     */
    public function strip() {
        return $this->addTransformation('strip');
    }

    /**
     * Add a thumbnail transformation
     *
     * @param int $width Width of the thumbnail
     * @param int $height Height of the thumbnail
     * @param string $fit Fit type. 'outbound' or 'inset'
     * @return self
     */
    public function thumbnail($width = 50, $height = 50, $fit = 'outbound') {
        return $this->addTransformation(
            sprintf('thumbnail:width=%d,height=%s,fit=%s', (int) $width, (int) $height, $fit)
        );
    }

    /**
     * Add a transpose transformation
     *
     * @return self
     */
    public function transpose() {
        return $this->addTransformation('transpose');
    }

    /**
     * Add a transverse transformation
     *
     * @return self
     */
    public function transverse() {
        return $this->addTransformation('transverse');
    }

    /**
     * Add a watermark transformation
     *
     * @param string $img The identifier of the image to be used as a watermark. Can be omitted if
     *                    the server is configured with a default watermark.
     * @param int $width The width of the watermark
     * @param int $height The height of the watermark
     * @param string $position The position of the watermark on the original image, 'top-left',
     *                         'top-right', 'bottom-left', 'bottom-right' or 'center'. Defaults to
     *                         'top-left'.
     * @param int $x Offset in the X-axis relative to the $position parameter. Defaults to 0
     * @param int $y Offset in the Y-axis relative to the $position parameter. Defaults to 0
     * @return self
     */
    public function watermark($img = null, $width = null, $height = null, $position = 'top-left', $x = 0, $y = 0) {
        $params = array(
            'position=' . $position,
            'x=' . (int) $x,
            'y=' . (int) $y,
        );

        if ($img !== null) {
            $params[] = 'img=' . $img;
        }

        if ($width !== null) {
            $params[] = 'width=' . (int) $width;
        }

        if ($height !== null) {
            $params[] = 'height=' . (int) $height;
        }

        return $this->addTransformation(sprintf('watermark:%s', implode(',', $params)));
    }

    /**
     * Convert to 'gif'
     *
     * @return self
     */
    public function gif() {
        return $this->convert('gif');
    }

    /**
     * Convert to 'jpg'
     *
     * @return self
     */
    public function jpg() {
        return $this->convert('jpg');
    }

    /**
     * Convert to 'png'
     *
     * @return self
     */
    public function png() {
        return $this->convert('png');
    }

    /**
     * Convert the URL to a string
     *
     * @return string
     */
    public function __toString() {
        // Update the path
        if ($this->extension) {
            $this->path = preg_replace('#(\.(gif|jpg|png))?$#', '.' . $this->extension, $this->path);
        }

        // Set the t query param, overriding it if it already exists, which it might do if the
        // string has already been converted to a string
        $this->query->set('t', $this->transformations);

        return parent::__toString();
    }

    /**
     * Reset the URL
     *
     * Effectively removes added transformations and an optional extension.
     *
     * @return self
     */
    public function reset() {
        if ($this->transformations) {
            // Remove image transformations
            $this->transformations = array();
            $this->query->remove('t');
        }

        if ($this->extension) {
            // Remove the extension
            $this->path = str_replace('.' . $this->extension, '', $this->path);
            $this->extension = null;
        }

        return $this;
    }
}
