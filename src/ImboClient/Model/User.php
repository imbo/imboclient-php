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
 * User resource model
 *
 * @package Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class User implements ResponseClassInterface {
    /**
     * @var DateTime
     */
    private $lastModified;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var int
     */
    private $numImages;

    /**
     * Factory method
     *
     * @param OperationCommand $command The current operation
     * @return User
     */
    public static function fromCommand(OperationCommand $command) {
        $user = $command->getResponse()->json();

        $date = new DateTime($user['lastModified'], new DateTimeZone('UTC'));

        return new self($user['publicKey'], $user['numImages'], $date);
    }

    /**
     * Class constructor
     *
     * @param string $publicKey The public key of the user
     * @param int $numImages The number of images the user has
     * @param DateTime $lastModified When the user was last modified
     */
    public function __construct($publicKey, $numImages, DateTime $lastModified) {
        $this->publicKey = $publicKey;
        $this->numImages = (int) $numImages;
        $this->lastModified = $lastModified;
    }

    /**
     * Fetch the public key
     *
     * @return string
     */
    public function getPublicKey() {
        return $this->publicKey;
    }

    /**
     * Fetch the number of images
     *
     * @return int
     */
    public function getNumImages() {
        return $this->numImages;
    }

    /**
     * Fetch the last modified date
     *
     * @return DateTime
     */
    public function getLastModified() {
        return $this->lastModified;
    }
}
