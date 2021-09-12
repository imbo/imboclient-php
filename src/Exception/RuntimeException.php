<?php declare(strict_types=1);
namespace ImboClient\Exception;

use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements ClientException
{
}
