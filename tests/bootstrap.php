<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClientTest;

use Guzzle\Tests\GuzzleTestCase,
    Guzzle\Service\Builder\ServiceBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Set a default service builder for the tests
$serviceDescription = require __DIR__ . '/../src/ImboClient/service.php';
GuzzleTestCase::setServiceBuilder(ServiceBuilder::factory($serviceDescription['operations']));

// Set the base path for respones mocks
GuzzleTestCase::setMockBasePath(__DIR__ . '/response_mocks');
