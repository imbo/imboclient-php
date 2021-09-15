<?php declare(strict_types=1);
namespace ImboClient\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements ClientException
{
}
