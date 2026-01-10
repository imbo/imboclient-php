<?php declare(strict_types=1);

namespace ImboClient\Exception;

use GuzzleHttp\Exception\BadResponseException;

class RequestException extends BadResponseException implements ClientException
{
}
