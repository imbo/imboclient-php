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
    ImboClient\Http\Response\ResponseInterface,
    RuntimeException as BaseRuntimeException;

/**
 * Runtime exception
 *
 * @package Exceptions
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class ServerException extends BaseRuntimeException implements Exception {
    /**
     * Response instance
     *
     * @var ResponseInterface
     */
    private $response;

    /**
     * Set the response instance
     *
     * @param ResponseInterface $response The response object containing info about the server
     *                                    response.
     * @return ServerException
     */
    public function setResponse(ResponseInterface $response) {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the response instance
     *
     * If the cURL driver causes an error that ends in the client not being able to set a proper
     * response this method must return null.
     *
     * @return ResponseInterface|null
     */
    public function getResponse() {
        return $this->response;
    }
}
