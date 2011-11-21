<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package ImboClient
 * @subpackage Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */

namespace ImboClient\Client\Driver;

use ImboClient\Http\Response\Response;
use ImboClient\Http\HeaderContainer;

/**
 * cURL client driver
 *
 * This class is a driver for the client using the cURL functions.
 *
 * @package ImboClient
 * @subpackage Client
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/imboclient-php
 */
class Curl implements DriverInterface {
    /**
     * The cURL handle used by the client
     *
     * @var resource
     */
    private $curlHandle;

    /**
     * Extra request headers
     *
     * @var array
     */
    private $headers = array();

    /**
     * Parameters for the driver
     *
     * @var array
     */
    private $params = array(
        'timeout'        => 2,
        'connectTimeout' => 2,
    );

    /**
     * Class constructor
     *
     * @param array $params Parameters for the driver
     */
    public function __construct(array $params = array()) {
        $this->curlHandle = curl_init();

        if (!empty($params)) {
            $this->params = array_merge($this->params, $params);
        }

        curl_setopt_array($this->curlHandle, array(
            CURLOPT_USERAGENT      => __CLASS__,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPHEADER     => array('Expect:'),
            CURLOPT_CONNECTTIMEOUT => $this->params['connectTimeout'],
            CURLOPT_TIMEOUT        => $this->params['timeout'],
        ));
    }

    /**
     * Class destructor
     */
    public function __destruct() {
        curl_close($this->curlHandle);
    }

    /**
     * @see ImboClient\Client\Driver\DriverInterface::post()
     */
    public function post($url, array $metadata = null) {
        $postFields = array(
            'metadata' => json_encode($metadata),
        );

        $handle = curl_copy_handle($this->curlHandle);

        curl_setopt_array($handle, array(
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $postFields,
        ));

        return $this->request($handle, $url);
    }

    /**
     * @see ImboClient\Client\Driver\DriverInterface::get()
     */
    public function get($url) {
        $handle = curl_copy_handle($this->curlHandle);

        curl_setopt_array($handle, array(
            CURLOPT_HTTPGET => true,
        ));

        return $this->request($handle, $url);
    }

    /**
     * @see ImboClient\Client\Driver\DriverInterface::head()
     */
    public function head($url) {
        $handle = curl_copy_handle($this->curlHandle);

        curl_setopt_array($handle, array(
            CURLOPT_NOBODY        => true,
            CURLOPT_CUSTOMREQUEST => 'HEAD',
        ));

        return $this->request($handle, $url);
    }

    /**
     * @see ImboClient\Client\Driver\DriverInterface::delete()
     */
    public function delete($url) {
        $handle = curl_copy_handle($this->curlHandle);

        curl_setopt_array($handle, array(
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ));

        return $this->request($handle, $url);
    }

    /**
     * @see ImboClient\Client\Driver\DriverInterface::put()
     */
    public function put($url, $filePath) {
        $fr = fopen($filePath, 'r');

        $handle = curl_copy_handle($this->curlHandle);

        curl_setopt_array($handle, array(
            CURLOPT_PUT        => true,
            CURLOPT_INFILE     => $fr,
            CURLOPT_INFILESIZE => filesize($filePath),
        ));

        return $this->request($handle, $url);
    }

    /**
     * @see ImboClient\Client\Driver\DriverInterface::addRequestHeader()
     */
    public function addRequestHeader($key, $value) {
        $this->headers[] = $key . ': ' . $value;

        return $this;
    }

    /**
     * Make a request
     *
     * This method will make a request to $url with the current options set in the cURL handle
     * resource.
     *
     * @param resource $handle A cURL handle
     * @param string $url The URL to request
     * @return ImboClient\Http\Response\ResponseInterface
     * @throws ImboClient\Client\Driver\Exception
     */
    protected function request($handle, $url) {
        curl_setopt_array($handle, array(
            CURLOPT_URL => $url,
        ));

        // Set extra headers
        if (!empty($this->headers)) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, $this->headers);
        }

        $content = curl_exec($handle);
        $connectTime  = (int) curl_getinfo($handle, CURLINFO_CONNECT_TIME);
        $transferTime = (int) curl_getinfo($handle, CURLINFO_TOTAL_TIME);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        if ($content === false) {
            if ($connectTime >= $this->params['connectTimeout']) {
                throw new Exception('An error occured. Request timed out while connecting (limit: ' . $this->params['connectTimeout'] . 's).');
            } else if ($transferTime >= $this->params['timeout']) {
                throw new Exception('An error occured. Request timed out during transfer (limit: ' . $this->params['timeout'] . 's).');
            }

            throw new Exception('An error occured. Could not complete request (Response code: ' . $statusCode . ').');
        }

        $content = str_replace("\r", '', $content);
        
        // Remove any HTTP/1.1 100 Continue from the response
        $content = preg_replace('/HTTP\/[.\d]+ 100.*?^HTTP/sm', 'HTTP', $content);
        
        list($headers, $body) = explode("\n\n", $content, 2);
        $headers = explode("\n", $headers);

        // Remove the first element (status line)
        $protocol = array_shift($headers);

        // Create a container for the headers
        $headerContainer = new HeaderContainer();

        // Loop through the rest of the headers and store them in the container
        foreach ($headers as $header) {
            list($key, $value) = explode(': ', $header, 2);
            $headerContainer->set($key, $value);
        }

        $response = new Response();
        $response->setBody($body)
                 ->setHeaders($headerContainer)
                 ->setStatusCode($statusCode);

        return $response;
    }
}
