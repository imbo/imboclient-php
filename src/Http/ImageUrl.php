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
class ImageUrl extends ImagesUrl {
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
     * Factory method
     *
     * @param string $url URL as a string
     * @param string $privateKey Optional private key
     * @param string $publicKey Optional public key
     * @return Url
     */
    public static function factory($url, $privateKey = null, $publicKey = null) {
        $url = parent::factory($url, $privateKey, $publicKey);
        $query = $url->getQuery();

        // Fetch transformations from string and add them as state
        $transformations = (array) $query->get('t');
        foreach ($query as $key => $value) {
            if (preg_match('#^t\[\d+\]$#', $key)) {
                $transformations[] = $value;
            }
        }

        $transformations = is_array($transformations) ? $transformations : array();

        foreach ($transformations as $transformation) {
            $url->addTransformation($transformation);
        }

        // Extract any extension and set it as state
        $pattern = '#^/users/[^/]+/images/[^\.]+(\.(?<extension>gif|jpg|png))?$#';
        if (preg_match($pattern, $url->getPath(), $match) && isset($match['extension'])) {
            $url->convert($match['extension']);
        }

        // Remove any existing access token (new one will be recreated when toString is called)
        $query->remove('accessToken');

        return $url;
    }

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
     * Add a blur transformation
     *
     * Parameters:
     *  `mode`   - `gaussian`, `adaptive`, `motion` or `radial`. Default: `guassian`
     *
     *  `radius` - Radius of the gaussian, in pixels, not counting the center pixel.
     *             Required for `gaussian`, `adaptive` and `motion`-modes
     *
     *  `sigma`  - The standard deviation of the gaussian, in pixels.
     *             Required for `gaussian`, `adaptive` and `motion`-modes
     *
     *  `angle`  - Angle of the radial blur. Only used in `radial`-mode.
     *
     * @param array $params Array of parameters
     * @return self
     */
    public function blur(array $params) {
        $mode = isset($params['mode']) ? $params['mode'] : null;
        $required = array('radius', 'sigma');

        if ($mode === 'motion') {
            $required[] = 'angle';
        } else if ($mode === 'radial') {
            $required = array('angle');
        }

        $transformation = $mode ? array('mode=' . $mode) : array();

        foreach ($required as $param) {
            if (!isset($params[$param])) {
                throw new InvalidArgumentException('`' . $param . '` must be specified');
            }

            $transformation[] = $param . '=' . $params[$param];
        }

        return $this->addTransformation(sprintf('blur:%s', implode(',', $transformation)));
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
     * Add a contrast transformation
     *
     * @param float $alpha Adjusts intensity differences between lighter and darker elements
     * @param float $beta Where the midpoint of the gradient will be. Range: 0 to 1
     * @return self
     */
    public function contrast($alpha = null, $beta = null) {
        $params = array();

        if ($alpha !== null) {
            $params[] = 'alpha=' . (float) $alpha;
        }

        if ($beta !== null) {
            $params[] = 'beta=' . (float) min(1, max(0, $beta));
        }

        return $this->addTransformation(
            'contrast' . ($params ? ':' . implode(',', $params) : '')
        );
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
     * Get the extension of the image
     *
     * @return string|null
     */
    public function getExtension() {
        return $this->extension;
    }

    /**
     * Get the added transformations
     *
     * @return array
     */
    public function getTransformations() {
        return $this->transformations;
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
     * Add a transformation that draws an outline around all the POIs (points of interest)
     * stored in the metadata for the image. The format of the metadata is documented under
     * the smartSize-transformation on the Imbo server API.
     *
     * @param string $color Color of the drawn outline
     * @param int $borderSize Thickness of the outline, in pixels
     * @param int $pointSize Diameter, in pixels, of the circle drawn
     *                       around POIs that do not have height and width specified
     * @return self
     */
    public function drawPois($color = null, $borderSize = null, $pointSize = null) {
        $params = array();

        if ($color) {
            $params[] = 'color=' . $color;
        }

        if ($borderSize) {
            $params[] = 'borderSize=' . (int) $borderSize;
        }

        if ($pointSize) {
            $params[] = 'pointSize=' . $pointSize;
        }

        return $this->addTransformation('drawPois' . ($params ? ':' . implode(',', $params) : ''));
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
     * Add a histogram transformation
     *
     * @param int $scale The amount to scale the histogram
     * @param float $ratio The ratio to use when calculating the height of the image
     * @param string $red The color to use when drawing the graph for the red channel
     * @param string $green The color to use when drawing the graph for the green channel
     * @param string $blue The color to use when drawing the graph for the blue channel
     * @return self
     */
    public function histogram($scale = null, $ratio = null, $red = null, $green = null, $blue = null) {
        $params = array();

        if ($scale) {
            $params[] = 'scale=' . (int) $scale;
        }

        if ($ratio) {
            $params[] = 'ratio=' . (float) $ratio;
        }

        if ($red) {
            $params[] = 'red=' . $red;
        }

        if ($green) {
            $params[] = 'green=' . $green;
        }

        if ($blue) {
            $params[] = 'blue=' . $blue;
        }

        return $this->addTransformation('histogram' . ($params ? ':' . implode(',', $params) : ''));
    }

    /**
     * Add a level transformation to the image, adjusting the levels of an image
     *
     * @param int $amount Amount to adjust, on a scale from -100 to 100
     * @param string $channel Optional channel to adjust. Possible values:
     *                        r, g, b, c, m, y, k - can be combined to adjust multiple.
     * @return self
     */
    public function level($amount = 1, $channel = null) {
        $params = array('amount=' . $amount);

        if ($channel) {
            $params[] = 'channel=' . $channel;
        }

        return $this->addTransformation('level:' . implode(',', $params));
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
     * Add a modulate transformation
     *
     * @param int $brightness Brightness of the image in percent
     * @param int $saturation Saturation of the image in percent
     * @param int $hue Hue percentage
     * @return self
     * @throws InvalidArgumentException
     */
    public function modulate($brightness = null, $saturation = null, $hue = null) {
        $params = array();

        if ($brightness) {
            $params[] = 'b=' . (int) $brightness;
        }

        if ($saturation) {
            $params[] = 's=' . (int) $saturation;
        }

        if ($hue) {
            $params[] = 'h=' . (int) $hue;
        }

        if (!$params) {
            throw new InvalidArgumentException('brightness, saturation and/or hue must be specified');
        }

        return $this->addTransformation(sprintf('modulate:%s', implode(',', $params)));
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
     * Add a sharpen transformation
     *
     * Parameters:
     *  `preset`    - `light`, `moderate`, `strong`, `extreme`.
     *  `radius`    - Radius of the gaussian, in pixels
     *  `sigma`     - Standard deviation of the gaussian, in pixels
     *  `threshold` - The threshold in pixels needed to apply the difference gain
     *  `gain`      - Percentage of difference between original and the blur image
     *                that is added back into the original
     *
     * @param array $params Parameters for the transformation
     * @return self
     */
    public function sharpen(array $params = null) {
        $options = array('preset', 'radius', 'sigma', 'threshold', 'gain');
        $transformation = array();

        foreach ($options as $param) {
            if (!isset($params[$param])) {
                continue;
            }

            $transformation[] = $param . '=' . $params[$param];
        }

        return $this->addTransformation(
            'sharpen' . ($transformation ? ':' . implode(',', $transformation) : '')
        );
    }

    /**
     * Add a smartSize transformation
     *
     * @param int $width Width of the resized image
     * @param int $height Height of the resized image
     * @param string $crop Closeness of crop (`close`, `medium` or `wide`). Optional.
     * @param string $poi POI-coordinate to crop around (as `x,y`).
     *                    Optional if POI-metadata exists for the image.
     * @return self
     */
    public function smartSize($width, $height, $crop = null, $poi = null) {
        if (!$width || !$height) {
            throw new InvalidArgumentException('width and height must be specified');
        }

        $params = array(
            'width=' . (int) $width,
            'height=' . (int) $height,
        );

        if ($crop) {
            $params[] = 'crop=' . $crop;
        }

        if ($poi) {
            $params[] = 'poi=' . $poi;
        }

        return $this->addTransformation(sprintf('smartSize:%s', implode(',', $params)));
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
     * Add a vignette transformation
     *
     * @param float $scale Scale factor of vignette. 2 means twice the size of the original image
     * @param string $outerColor Color at the edge of the image
     * @param string $innerColor Color at the center of the image
     * @return self
     */
    public function vignette($scale = null, $outerColor = null, $innerColor = null) {
        $params = array();

        if ($scale) {
            $params[] = 'scale=' . $scale;
        }

        if ($outerColor) {
            $params[] = 'outer=' . $outerColor;
        }

        if ($innerColor) {
            $params[] = 'inner=' . $innerColor;
        }

        return $this->addTransformation(
            'vignette' . ($params ? ':' . implode(',', $params) : '')
        );
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
            // Remove a possible extension in the path, and append the new one
            $this->path = preg_replace('#(\.(gif|jpg|png))$#', '', $this->path) . '.' . $this->extension;
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

    /**
     * Fetch the image identifier in the URL
     *
     * @return string|null
     */
    public function getImageIdentifier() {
        if (preg_match('#/users/[^/]+/images/(?<imgId>[^./]+)#', $this->getPath(), $match)) {
            return $match['imgId'];
        }

        return null;
    }
}
