<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Driver\cURL;

/**
 * Wrapper for some of the curl_* functions
 *
 * @package ImboClient\Drivers\cURL
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @codeCoverageIgnore
 */
class Wrapper {
    /**
     * @var resource
     */
    private $handle;

    /**
     * Class constructor
     *
     * @param boolean $init Whether or not to initialize the cURL handle
     */
    public function __construct($init = true) {
        if ($init) {
            $this->init();
        }
    }

    /**
     * Initialize the current handle
     */
    public function init() {
        if ($this->handle) {
            $this->close();
        }

        $this->handle = curl_init();
    }

    /**
     * Set multiple options
     *
     * @param array $options An array of options
     * @param resource $handle An optional cURL handle
     * @return boolean
     */
    public function setOptArray(array $options, $handle = null) {
        curl_setopt_array($handle ?: $this->handle, $options);
    }

    /**
     * Close the current handle
     *
     * @param resource $handle Optional cURL handle
     */
    public function close($handle = null) {
        curl_close($handle ?: $this->handle);
    }

    /**
     * Copy the current handle
     *
     * @param resource $handle Optional cURL handle
     * @return resource
     */
    public function copy($handle = null) {
        return curl_copy_handle($handle ?: $this->handle);
    }

    /**
     * Set an option
     *
     * @param int $opt A CURLOPT_* constant
     * @param mixed $value The value to set
     * @param resource $handle Optional cURL handle
     * @return boolean
     */
    public function setOpt($opt, $value, $handle = null) {
        return curl_setopt($handle ?: $this->handle, $opt, $value);
    }

    /**
     * Execute a handle
     *
     * @param resource $handle Optional cURL handle
     * @return mixed
     */
    public function exec($handle = null) {
        return curl_exec($handle ?: $this->handle);
    }

    /**
     * Fetch info from the handle
     *
     * @param string $opt One of the CURLINFO_* constants
     * @param resource $handle Optional cURL handle
     * @return mixed
     */
    public function getInfo($opt, $handle = null) {
        return curl_getinfo($handle ?: $this->handle, $opt);
    }
}
