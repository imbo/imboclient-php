<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient;

/**
 * Interface for the client
 *
 * @package Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface ImboClientInterface {
    /**
     * Fetch status about the Imbo server
     *
     * @return array
     */
    function getServerStatus();

    /**
     * Fetch user info
     *
     * @return array
     */
    function getUserInfo();

    /**
     * Factory method for creating a new ImboClient instance
     *
     * Configuration parameters:
     *
     * - (string) baseUrl: Base URL to the imbo server
     * - (string) publicKey: The public key to use
     * - (string) privateKey: The private key to use
     *
     * @param array|Collection $config Configuration for the client
     * @return ImboClientInterface
     */
    static function factory($config = array());
}
