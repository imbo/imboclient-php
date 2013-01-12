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
 * Header container
 *
 * This container contains HTTP headers along with some methods for normalizing the header names.
 *
 * @package Http
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class HeaderContainer implements HeaderContainerInterface {
    /**
     * Parameters in the container
     *
     * @var array
     */
    private $parameters;

    /**
     * Class constructor
     *
     * @param array $parameters Parameters to store in the container
     */
    public function __construct(array $parameters = array()) {
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAll() {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value) {
        $key = $this->getName($key);
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null) {
        $key = $this->getName($key);

        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key) {
        $key = $this->getName($key);
        unset($this->parameters[$key]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key) {
        $key = $this->getName($key);

        return isset($this->parameters[$key]);
    }

    /**
     * Normalize the header name
     *
     * @param string $name The name to normalize, for instance "IF_MODIFIED_SINCE"
     * @return string The normalized name, for instance "if-modified-since"
     */
    private function getName($name) {
        return strtolower(str_replace('_', '-', $name));
    }
}
