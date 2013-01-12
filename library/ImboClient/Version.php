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
 * Version class
 *
 * @package ImboClient\Version
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Version {
    /**
     * The current version
     *
     * @var string
     */
    const VERSION = 'dev';
    private static $version;

    /**
     * Get the version "number" only
     *
     * @return string
     */
    public function getVersionNumber() {
        if (self::$version === null) {
            self::$version = self::VERSION;

            if (self::$version === 'dev' && is_dir(__DIR__ . '/../../.git')) {
                // We have a git checkout. Add commit hash
                $current = getcwd();
                chdir(__DIR__ . '/../../.git');
                $hash = exec('git rev-parse --short HEAD');
                self::$version .= '-' . $hash;
                chdir($current);
            }
        }

        return self::$version;
    }

    /**
     * Get the version string
     *
     * @return string
     */
    public function getVersionString() {
        return 'ImboClient-php-' . $this->getVersionNumber();
    }
}
