<?php declare(strict_types=1);
namespace ImboClient\Uri;

use ImboClient\Exception\InvalidImageTransformationException;

class ImageUri extends AccessTokenUri
{
    /** @var array<string> */
    private array $validExtensions = [
        'gif',
        'jpg',
        'png',
    ];

    public function autorotate(): self
    {
        return $this->withTransformation('autoRotate');
    }

    public function blur(array $params): self
    {
        $mode = array_key_exists('mode', $params) ? (string) $params['mode'] : null;
        $required = ['radius', 'sigma'];

        if ('motion' === $mode) {
            $required[] = 'angle';
        } elseif ('radial' === $mode) {
            $required = ['angle'];
        }

        $transformation = $mode ? ['mode=' . $mode] : [];

        foreach ($required as $param) {
            if (!array_key_exists($param, $params)) {
                throw new InvalidImageTransformationException($param . ' must be specified');
            }

            $transformation[] = $param . '=' . (string) $params[$param];
        }

        return $this->withTransformation('blur:' . implode(',', $transformation));
    }

    public function border(string $color = '000000', int $width = 1, int $height = 1, string $mode = 'outbound'): self
    {
        return $this->withTransformation(
            'border:color=' . $color . ',width=' . $width . ',height=' . $height . ',mode=' . $mode,
        );
    }

    public function canvas(int $width, int $height, string $mode = null, int $x = null, int $y = null, string $bg = null): self
    {
        if (0 >= $width || 0 >= $height) {
            throw new InvalidImageTransformationException('width and height must be positive');
        }

        $params = [
            'width=' . $width,
            'height=' . $height,
        ];

        if (null !== $mode) {
            $params[] = 'mode=' . $mode;
        }

        if (null !== $x) {
            $params[] = 'x=' . $x;
        }

        if (null !== $y) {
            $params[] = 'y=' . $y;
        }

        if (null !== $bg) {
            $params[] = 'bg=' . $bg;
        }

        return $this->withTransformation('canvas:' . implode(',', $params));
    }

    public function compress(int $level = 75): self
    {
        return $this->withTransformation('compress:level=' . $level);
    }

    public function contrast(float $alpha = null, float $beta = null): self
    {
        $params = [];

        if (null !== $alpha) {
            $params[] = 'alpha=' . $alpha;
        }

        if (null !== $beta) {
            $params[] = 'beta=' . min(1, max(0, $beta));
        }

        return $this->withTransformation(
            'contrast' . (count($params) ? ':' . implode(',', $params) : ''),
        );
    }

    public function convert(string $extension): self
    {
        if (!in_array($extension, $this->validExtensions)) {
            throw new InvalidImageTransformationException('Extension ' . $extension . ' is not supported');
        }

        $extensions = implode('|', $this->validExtensions);
        $pathWithNoExtension = preg_replace('#(\.(' . $extensions . '))$#', '', $this->getPath());

        return $this->withPath(
            $pathWithNoExtension . '.' . $extension,
        );
    }

    public function crop(int $x = null, int $y = null, int $width = null, int $height = null, string $mode = null): self
    {
        if (null === $mode && (null === $x || null === $y)) {
            throw new InvalidImageTransformationException('x and y needs to be specified without a crop mode');
        }

        if ('center-x' === $mode && null === $y) {
            throw new InvalidImageTransformationException('y needs to be specified when mode is center-x');
        }

        if ('center-y' === $mode && null === $x) {
            throw new InvalidImageTransformationException('x needs to be specified when mode is center-y');
        }

        if (null === $width || null === $height) {
            throw new InvalidImageTransformationException('width and height needs to be specified');
        }

        $params = [
            'width=' . $width,
            'height=' . $height,
        ];

        if (null !== $x) {
            $params[] = 'x=' . $x;
        }

        if (null !== $y) {
            $params[] = 'y=' . $y;
        }

        if (null !== $mode) {
            $params[] = 'mode=' . $mode;
        }

        return $this->withTransformation('crop:' . implode(',', $params));
    }

    public function desaturate(): self
    {
        return $this->withTransformation('desaturate');
    }

    public function drawPois(string $color = null, int $borderSize = null, int $pointSize = null): self
    {
        $params = [];

        if (null !== $color) {
            $params[] = 'color=' . $color;
        }

        if (null !== $borderSize) {
            $params[] = 'borderSize=' . $borderSize;
        }

        if (null !== $pointSize) {
            $params[] = 'pointSize=' . $pointSize;
        }

        return $this->withTransformation('drawPois' . ($params ? ':' . implode(',', $params) : ''));
    }

    public function extremeSharpen(): self
    {
        return $this->withTransformation('sharpen:extreme');
    }

    public function flipHorizontally(): self
    {
        return $this->withTransformation('flipHorizontally');
    }

    public function flipVertically(): self
    {
        return $this->withTransformation('flipVertically');
    }

    public function gif(): self
    {
        return $this->convert('gif');
    }

    public function histogram(int $scale = null, float $ratio = null, string $red = null, string $green = null, string $blue = null): self
    {
        $params = [];

        if (null !== $scale) {
            $params[] = 'scale=' . $scale;
        }

        if (null !== $ratio) {
            $params[] = 'ratio=' . $ratio;
        }

        if (null !== $red) {
            $params[] = 'red=' . $red;
        }

        if (null !== $green) {
            $params[] = 'green=' . $green;
        }

        if (null !== $blue) {
            $params[] = 'blue=' . $blue;
        }

        return $this->withTransformation('histogram' . ($params ? ':' . implode(',', $params) : ''));
    }

    public function jpg(): self
    {
        return $this->convert('jpg');
    }

    public function level(int $amount = 1, string $channel = null): self
    {
        $params = ['amount=' . $amount];

        if (null !== $channel) {
            $params[] = 'channel=' . $channel;
        }

        return $this->withTransformation('level:' . implode(',', $params));
    }

    public function maxSize(int $maxWidth = null, int $maxHeight = null): self
    {
        $params = [];

        if (null !== $maxWidth) {
            $params[] = 'width=' . $maxWidth;
        }

        if (null !== $maxHeight) {
            $params[] = 'height=' . $maxHeight;
        }

        if (!count($params)) {
            throw new InvalidImageTransformationException('width and/or height must be specified');
        }

        return $this->withTransformation('maxSize:' . implode(',', $params));
    }

    public function moderateSharpen(): self
    {
        return $this->withTransformation('sharpen:moderate');
    }

    public function modulate(int $brightness = null, int $saturation = null, int $hue = null): self
    {
        $params = [];

        if (null !== $brightness) {
            $params[] = 'b=' . $brightness;
        }

        if (null !== $saturation) {
            $params[] = 's=' . $saturation;
        }

        if (null !== $hue) {
            $params[] = 'h=' . $hue;
        }

        if (!count($params)) {
            throw new InvalidImageTransformationException('brightness, saturation and/or hue must be specified');
        }

        return $this->withTransformation('modulate:' . implode(',', $params));
    }

    public function png(): self
    {
        return $this->convert('png');
    }

    public function progressive(): self
    {
        return $this->withTransformation('progressive');
    }

    public function resize(int $width = null, int $height = null): self
    {
        $params = [];

        if (null !== $width) {
            $params[] = 'width=' . $width;
        }

        if (null !== $height) {
            $params[] = 'height=' . $height;
        }

        if (!count($params)) {
            throw new InvalidImageTransformationException('width and/or height must be specified');
        }

        return $this->withTransformation('resize:' . implode(',', $params));
    }

    public function rotate(float $angle, string $bg = '000000'): self
    {
        if (0 >= $angle) {
            throw new InvalidImageTransformationException('angle must be positive');
        }

        return $this->withTransformation('rotate:angle=' . $angle . ',bg=' . $bg);
    }

    public function sepia(int $threshold = 80): self
    {
        return $this->withTransformation('sepia:threshold=' . $threshold);
    }

    public function sharpen(int $radius = null, int $sigma = null, int $gain = null, float $threshold = null): self
    {
        $params = [];

        if (null !== $radius) {
            $params[] = 'radius=' . $radius;
        }

        if (null !== $sigma) {
            $params[] = 'sigma=' . $sigma;
        }

        if (null !== $gain) {
            $params[] = 'gain=' . $gain;
        }

        if (null !== $threshold) {
            $params[] = 'threshold=' . $threshold;
        }

        return $this->withTransformation(
            'sharpen' . ($params ? ':' . implode(',', $params) : ''),
        );
    }

    public function smartSize(int $width, int $height, string $crop = null, string $poi = null): self
    {
        if (0 >= $width || 0 >= $height) {
            throw new InvalidImageTransformationException('width and height must be positive');
        }

        $params = [
            'width=' . $width,
            'height=' . $height,
        ];

        if (null !== $crop) {
            $params[] = 'crop=' . $crop;
        }

        if (null !== $poi) {
            $params[] = 'poi=' . $poi;
        }

        return $this->withTransformation('smartSize:' . implode(',', $params));
    }

    public function strip(): self
    {
        return $this->withTransformation('strip');
    }

    public function strongSharpen(): self
    {
        return $this->withTransformation('sharpen:strong');
    }

    public function thumbnail(int $width = 50, int $height = 50, string $fit = 'outbound'): self
    {
        return $this->withTransformation(
            'thumbnail:width=' . $width . ',height=' . $height . ',fit=' . $fit,
        );
    }

    public function transpose(): self
    {
        return $this->withTransformation('transpose');
    }

    public function transverse(): self
    {
        return $this->withTransformation('transverse');
    }

    public function vignette(float $scale = null, string $outerColor = null, string $innerColor = null): self
    {
        $params = [];

        if (null !== $scale) {
            $params[] = 'scale=' . $scale;
        }

        if (null !== $outerColor) {
            $params[] = 'outer=' . $outerColor;
        }

        if (null !== $innerColor) {
            $params[] = 'inner=' . $innerColor;
        }

        return $this->withTransformation(
            'vignette' . ($params ? ':' . implode(',', $params) : ''),
        );
    }

    public function watermark(string $img = null, int $width = null, int $height = null, string $position = 'top-left', int $x = 0, int $y = 0): self
    {
        $params = [
            'position=' . $position,
            'x=' . $x,
            'y=' . $y,
        ];

        if (null !== $img) {
            $params[] = 'img=' . $img;
        }

        if (null !== $width) {
            $params[] = 'width=' . $width;
        }

        if (null !== $height) {
            $params[] = 'height=' . $height;
        }

        return $this->withTransformation('watermark:' . implode(',', $params));
    }

    public function withTransformation(string $transformation): self
    {
        parse_str($this->getQuery(), $query);

        if (!array_key_exists('t', $query) || !is_array($query['t'])) {
            $query['t'] = [];
        }

        $query['t'][] = $transformation;
        return $this->withQuery(http_build_query($query));
    }

    public function reset(): self
    {
        $pathWithNoExtension = preg_replace(
            '#(\.(' . implode('|', $this->validExtensions) . '))$#',
            '',
            $this->getPath(),
        );
        return $this
            ->withPath($pathWithNoExtension)
            ->withQuery('');
    }
}
