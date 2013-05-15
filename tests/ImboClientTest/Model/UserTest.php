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

use ImboClient\Model\User,
    DateTime;

/**
 * @package Test suite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class UserTest extends \PHPUnit_Framework_TestCase {
    /**
     * @covers ImboClient\Model\User::fromCommand
     */
    public function testCreatesAnInstanceFromACommand() {
        $apiResponse = <<<JSON
{
  "publicKey": "christer",
  "numImages": 123,
  "lastModified": "Tue, 09 Apr 2013 07:00:18 GMT"
}
JSON;
        $output = json_decode($apiResponse, true);

        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')->disableOriginalConstructor()->getMock();
        $response->expects($this->once())->method('json')->will($this->returnValue($output));

        $command = $this->getMock('Guzzle\Service\Command\OperationCommand');
        $command->expects($this->once())->method('getResponse')->will($this->returnValue($response));

        $user = User::fromCommand($command);

        $this->assertSame('christer', $user->getPublicKey());
        $this->assertSame(123, $user->getNumImages());
        $this->assertInstanceOf('DateTime', $user->getLastModified());
        $this->assertSame('2013-04-09 07:00:18', $user->getLastModified()->format('Y-m-d H:i:s'));
    }

    /**
     * @covers ImboClient\Model\User::__construct
     * @covers ImboClient\Model\User::getPublicKey
     * @covers ImboClient\Model\User::getNumImages
     * @covers ImboClient\Model\User::getLastModified
     */
    public function testCorrectlyPopulatesProperties() {
        $publicKey = 'christer';
        $numImages = 123;
        $lastModified = new DateTime();

        $user = new User($publicKey, $numImages, $lastModified);
        $this->assertSame($publicKey, $user->getPublicKey());
        $this->assertSame($numImages, $user->getNumImages());
        $this->assertSame($lastModified, $user->getLastModified());
    }
}
