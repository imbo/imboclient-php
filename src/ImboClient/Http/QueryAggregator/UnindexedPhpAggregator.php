<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */
namespace ImboClient\Http\QueryAggregator;

use Guzzle\Http\QueryAggregator\QueryAggregatorInterface,
    Guzzle\Http\QueryString;

/**
 * Aggregates nested query string variables using PHP style [],
 * without any numeric indexes ([] vs [0])
 *
 * @package Client\Http\QueryAggregator
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class UnindexedPhpAggregator implements QueryAggregatorInterface {
    public function aggregate($key, $value, QueryString $query) {
        if ($query->isUrlEncoding()) {
            return array($query->encodeValue($key . '[]') => array_map(array($query, 'encodeValue'), $value));
        } else {
            return array($key . '[]' => $value);
        }
    }
}
