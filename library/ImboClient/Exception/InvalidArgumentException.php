<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Exception;

use ImboClient\Exception,
    InvalidArgumentException as BaseInvalidArgumentException;

/**
 * Invalid argument exception
 *
 * @package ImboClient\Exceptions
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class InvalidArgumentException extends BaseInvalidArgumentException implements Exception {}
