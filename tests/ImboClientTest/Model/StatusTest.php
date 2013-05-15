<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClientTest\Model;

use ImboClient\Model\Status,
    DateTime;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class StatusTest extends \PHPUnit_Framework_TestCase {
    /**
     * @covers ImboClient\Model\Status::fromCommand
     */
    public function testCreatesAnInstanceFromACommand() {
        $apiResponse = <<<JSON
{
  "date": "Tue, 30 Apr 2013 06:01:19 GMT",
  "database": true,
  "storage": true
}
JSON;
        $output = json_decode($apiResponse, true);

        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')->disableOriginalConstructor()->getMock();
        $response->expects($this->once())->method('json')->will($this->returnValue($output));

        $command = $this->getMock('Guzzle\Service\Command\OperationCommand');
        $command->expects($this->once())->method('getResponse')->will($this->returnValue($response));

        $status = Status::fromCommand($command);

        $this->assertInstanceOf('DateTime', $status->getDate());
        $this->assertSame('2013-04-30 06:01:19', $status->getDate()->format('Y-m-d H:i:s'));
        $this->assertTrue($status->getDatabaseStatus());
        $this->assertTrue($status->getStorageStatus());
    }

    /**
     * @covers ImboClient\Model\Status::__construct
     * @covers ImboClient\Model\Status::getDate
     * @covers ImboClient\Model\Status::getDatabaseStatus
     * @covers ImboClient\Model\Status::getStorageStatus
     */
    public function testCorrectlyPopulatesProperties() {
        $date = new DateTime();
        $database = true;
        $storage = false;

        $status = new Status($date, $database, $storage);
        $this->assertSame($date, $status->getDate());
        $this->assertSame($database, $status->getDatabaseStatus());
        $this->assertSame($storage, $status->getStorageStatus());
    }
}
