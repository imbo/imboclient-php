<?php
namespace ImboClient\Http\QueryAggregator;

use Guzzle\Http\QueryAggregator\QueryAggregatorInterface,
    Guzzle\Http\QueryString;

/**
 * Aggregates nested query string variables using PHP style [],
 * without any numeric indexes ([] vs [0])
 *
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
