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
    Guzzle\Service\Command\OperationCommand,
    DateTime,
    DateTimeZone;

/**
 * Status resource model
 *
 * @package Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Status implements ResponseClassInterface {
    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var boolean
     */
    private $databaseStatus;

    /**
     * @var boolean
     */
    private $storageStatus;

    /**
     * Factory method
     *
     * @param OperationCommand $command The current operation
     * @return Status
     */
    public static function fromCommand(OperationCommand $command) {
        $status = $command->getResponse()->json();

        $date = new DateTime($status['date'], new DateTimeZone('UTC'));

        return new self($date, $status['database'], $status['storage']);
    }

    /**
     * Class constructor
     *
     * @param DateTime $date The date from the status as a DateTime instance
     * @param boolean $databaseStatus Flag with info on whether or not the database is up
     * @param boolean $storageStatus Flag with info on whether or not the storage is up
     */
    public function __construct(DateTime $date, $databaseStatus, $storageStatus) {
        $this->date = $date;
        $this->databaseStatus = (boolean) $databaseStatus;
        $this->storageStatus = (boolean) $storageStatus;
    }

    /**
     * Fetch the date
     *
     * @return DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Fetch the database status flag
     *
     * @return boolean
     */
    public function getDatabaseStatus() {
        return $this->databaseStatus;
    }

    /**
     * Fetch the storage status flag
     *
     * @return boolean
     */
    public function getStorageStatus() {
        return $this->storageStatus;
    }
}
