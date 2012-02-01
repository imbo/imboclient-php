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
 * @package Http
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient\Http;

/**
 * Header container
 *
 * This container contains HTTP headers along with some methods for normalizing the header names.
 *
 * @package Http
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
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
     * @see ImboClient\Http\HeaderContainerInterface::getAll()
     */
    public function getAll() {
        return $this->parameters;
    }

    /**
     * @see ImboClient\Http\HeaderContainerInterface::set()
     */
    public function set($key, $value) {
        $key = $this->getName($key);
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * @see ImboClient\Http\HeaderContainerInterface::get()
     */
    public function get($key, $default = null) {
        $key = $this->getName($key);

        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }

    /**
     * @see ImboClient\Http\HeaderContainerInterface::remove()
     */
    public function remove($key) {
        $key = $this->getName($key);
        unset($this->parameters[$key]);

        return $this;
    }

    /**
     * @see ImboClient\Http\HeaderContainerInterface::has()
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
