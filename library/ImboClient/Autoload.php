<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
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
 * @subpackage Autoload
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient;

/**
 * Autoloader used by ImboClient
 *
 * @package Client
 * @subpackage Autoload
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */
class Autoload {
    /**
     * ImboClient classes
     *
     * @var array
     */
    static public $classes = array(
        'imboclient\\autoload' => '/Autoload.php',
        'imboclient\\client' => '/Client.php',
        'imboclient\\client\\driver\\curl' => '/Client/Driver/Curl.php',
        'imboclient\\client\\driver\\driverinterface' => '/Client/Driver/DriverInterface.php',
        'imboclient\\clientinterface' => '/ClientInterface.php',
        'imboclient\\http\\headercontainer' => '/Http/HeaderContainer.php',
        'imboclient\\http\\headercontainerinterface' => '/Http/HeaderContainerInterface.php',
        'imboclient\\http\\response\\response' => '/Http/Response/Response.php',
        'imboclient\\http\\response\\responseinterface' => '/Http/Response/ResponseInterface.php',
        'imboclient\\imageurl\\imageurl' => '/ImageUrl/ImageUrl.php',
        'imboclient\\imageurl\\imageurlinterface' => '/ImageUrl/ImageUrlInterface.php'
    );

    /**
     * Load a class
     *
     * @param string $class The name of the class to load
     */
    static public function load($class) {
        $className = strtolower($class);

        if (isset(static::$classes[$className])) {
            require __DIR__ . static::$classes[$className];
        }
    }

    /**
     * Registers this instance as an autoloader
     *
     * @codeCoverageIgnore
     */
    public function register() {
        // Register the autoloader
        spl_autoload_register(array($this, 'load'));
    }
}
