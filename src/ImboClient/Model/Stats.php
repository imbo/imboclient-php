<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Model;

use Guzzle\Service\Command\ResponseClassInterface,
    Guzzle\Service\Command\OperationCommand;

/**
 * Stats resource model
 *
 * @package Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Stats implements ResponseClassInterface {
    /**
     * Server stats
     *
     * @var array
     */
    private $stats = array();

    /**
     * Factory method
     *
     * @param OperationCommand $command The current operation
     * @return Stats
     */
    public static function fromCommand(OperationCommand $command) {
        return new self($command->getResponse()->json());
    }

    /**
     * Class constructor
     *
     * @param array $stats The stats from the server
     */
    public function __construct(array $stats) {
        $this->stats = $stats;
    }

    /**
     * Get the user stats
     *
     * @param string $publicKey Specify a public key to get the stats for this user
     * @return array|null
     */
    public function getUserStats($publicKey = null) {
        if ($publicKey === null) {
            return $this->stats['users'];
        }

        if (isset($this->stats['users'][$publicKey])) {
            return $this->stats['users'][$publicKey];
        }

        return null;
    }

    /**
     * Get the totals
     *
     * @return array
     */
    public function getTotals() {
        return $this->stats['total'];
    }

    /**
     * Get custom stats
     *
     * @return array
     */
    public function getCustomStats() {
        return $this->stats['custom'];
    }
}
