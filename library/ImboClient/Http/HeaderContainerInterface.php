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
 * Parameter container interface
 *
 * @package Http
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface HeaderContainerInterface {
    /**
     * Get all parameters as an associative array
     *
     * @return array
     */
    function getAll();

    /**
     * Set a parameter value
     *
     * @param string $key The key to store the value to
     * @param mixed $value The value itself
     * @return HeaderContainerInterface
     */
    function set($key, $value);

    /**
     * Get a parameter value
     *
     * @param string $key The key to fetch
     * @param mixed $default If the key does not exist, return this value instead
     * @return mixed
     */
    function get($key, $default = null);

    /**
     * Remove a single value from the parameter list
     *
     * @param string $key The key to remove
     * @return HeaderContainerInterface
     */
    function remove($key);

    /**
     * See if the container has a given key
     *
     * @param string $key The key to check for
     * @return boolean
     */
    function has($key);
}
