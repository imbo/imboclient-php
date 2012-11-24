<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
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
 * @package ImboClient\Drivers
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

namespace ImboClient\Driver;

use ImboClient\Http\Response\Response,
    ImboClient\Http\Response\ResponseInterface,
    ImboClient\Http\HeaderContainer,
    ImboClient\Exception\ServerException,
    ImboClient\Exception\RuntimeException;

/**
 * cURL client driver
 *
 * This class is a driver for the client using the cURL functions.
 *
 * @package ImboClient\Drivers
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */
class cURL implements DriverInterface {
    /**
     * The cURL handle used by the client
     *
     * @var resource
     */
    private $curlHandle;

    /**
     * Options for cURL
     *
     * @var array
     */
    private $curlOptions;

    /**
     * Request headers
     *
     * @var array
     */
    private $headers = array(
        'Expect:',
    );

    /**
     * Parameters for the driver
     *
     * @var array
     */
    private $params = array(
        // Timeout options
        'timeout'        => 2,
        'connectTimeout' => 2,

        // SSL options
        'sslVerifyPeer'  => true,
        'sslVerifyHost'  => 2,
        'sslCaInfo'      => null,
        'sslCaPath'      => null,
    );

    /**
     * Class constructor
     *
     * @param array $params Parameters for the driver
     * @param array $curlOptions Optional extra cURL options (ref: http://no2.php.net/curl_setopt)
     */
    public function __construct(array $params = array(), array $curlOptions = array()) {
        $this->curlHandle = curl_init();

        if (!empty($params)) {
            $this->params = array_merge($this->params, $params);
        }

        // Default cURL options
        $this->curlOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_CONNECTTIMEOUT => $this->params['connectTimeout'],
            CURLOPT_TIMEOUT        => $this->params['timeout'],
            CURLOPT_FOLLOWLOCATION => true,
        );

        if (!empty($curlOptions)) {
            // Merge with user specified options, overwriting default values
            $this->curlOptions = $curlOptions + $this->curlOptions;
        }

        curl_setopt_array($this->curlHandle, $this->curlOptions);
    }

    /**
     * Class destructor
     */
    public function __destruct() {
        curl_close($this->curlHandle);
    }

    /**
     * {@inheritdoc}
     */
    public function post($url, $metadata, array $headers = array()) {
        $handle = curl_copy_handle($this->curlHandle);

        curl_setopt_array($handle, array(
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $metadata,
        ));

        return $this->request($handle, $url, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function get($url) {
        $handle = curl_copy_handle($this->curlHandle);

        curl_setopt_array($handle, array(
            CURLOPT_HTTPGET => true,
        ));

        return $this->request($handle, $url);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function delete($url) {
        $handle = curl_copy_handle($this->curlHandle);

        curl_setopt_array($handle, array(
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ));

        return $this->request($handle, $url);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function putData($url, $data, array $headers = array()) {
        $handle = curl_copy_handle($this->curlHandle);

        curl_setopt_array($handle, array(
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS    => $data,
        ));

        return $this->request($handle, $url, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHeader($key, $value) {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHeaders(array $headers) {
        foreach ($headers as $key => $value) {
            $this->setRequestHeader($key, $value);
        }

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
     * @param array $headers Additional headers to send in this request as an associative array
     * @return ResponseInterface
     * @throws RuntimeException|ServerException
     */
    protected function request($handle, $url, array $headers = array()) {
        // Initialize options for the cURL handle
        $options = array(CURLOPT_URL => $url);

        if (substr($url, 0, 8) === 'https://') {
            // Add SSL options (not overwriting options already set)
            $options += array(
                CURLOPT_SSL_VERIFYPEER => $this->params['sslVerifyPeer'],
                CURLOPT_SSL_VERIFYHOST => $this->params['sslVerifyHost'],
            );

            if ($this->params['sslCaInfo']) {
                $options[CURLOPT_CAINFO] = $this->params['sslCaInfo'];
            }

            if ($this->params['sslCaPath']) {
                $options[CURLOPT_CAPATH] = $this->params['sslCaPath'];
            }
        }

        curl_setopt_array($handle, $options);

        // Set extra headers
        $headers = array_merge($this->headers, $headers);
        $requestHeaders = array();

        foreach ($headers as $key => $value) {
            $requestHeaders[] = $key . ':' . $value;
        }

        curl_setopt($handle, CURLOPT_HTTPHEADER, $requestHeaders);

        $content      = curl_exec($handle);
        $connectTime  = (int) curl_getinfo($handle, CURLINFO_CONNECT_TIME);
        $transferTime = (int) curl_getinfo($handle, CURLINFO_TOTAL_TIME);
        $statusCode   = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        if ($content === false) {
            if ($connectTime >= $this->params['connectTimeout']) {
                throw new RuntimeException('An error occured. Request timed out while connecting (limit: ' . $this->params['connectTimeout'] . 's).');
            } else if ($transferTime >= $this->params['timeout']) {
                throw new RuntimeException('An error occured. Request timed out during transfer (limit: ' . $this->params['timeout'] . 's).');
            }

            throw new RuntimeException('An error occured. Could not complete request (Response code: ' . $statusCode . ').');
        }

        // Remove any HTTP/1.1 100 Continue from the response
        $content = preg_replace('/HTTP\/[.\d]+ 100 .*?\r\n\r\n/sm', '', $content);

        // cURL will include headers from all requests if hitting 3xx responses (and CURLOPT_FOLLOWLOCATION is on)
        // Strip away all 3xx-header sets from the content, leaving only the last set of headers and the body
        $content = preg_replace('/^HTTP\/[.\d]+ 3\d+.*?\r\n\r\n/sm', '', $content);

        list($headers, $body) = explode("\r\n\r\n", $content, 2);

        $headers = str_replace("\r", '', $headers);
        $headers = explode("\n", $headers);

        // Remove the first element (status line)
        array_shift($headers);

        // Create a container for the headers
        $headerContainer = new HeaderContainer();

        // Loop through the rest of the headers and store them in the container
        foreach ($headers as $header) {
            list($key, $value) = explode(': ', $header, 2);
            $headerContainer->set($key, $value);
        }

        // Create the response instance
        $response = new Response();
        $response->setBody($body)
                 ->setHeaders($headerContainer)
                 ->setStatusCode($statusCode);

        if ($response->isError()) {
            if (!empty($body)) {
                $body = json_decode($body);
                $errorMessage = $body->error->message;
            } else {
                $errorMessage = 'Empty body';
            }

            // The server responded with some sort of error
            $exception = new ServerException($errorMessage, $statusCode);
            $exception->setResponse($response);

            throw $exception;
        }

        return $response;
    }
}
