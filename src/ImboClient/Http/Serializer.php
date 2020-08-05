<?php

namespace ImboClient\Http;

use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Guzzle\SchemaValidator;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\Serializer as DefaultSerializer;
use Psr\Http\Message\RequestInterface;

/**
 * Override Request serializer include BodyLocation
 */

class Serializer extends DefaultSerializer
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        DescriptionInterface $description,
        array $requestLocations = []
    ) {
        // Override Guzzle's body location as it isn't raw binary data
        $requestLocations['body'] = new Request\BodyLocation;
        parent::__construct($description, $requestLocations);
    }
}
