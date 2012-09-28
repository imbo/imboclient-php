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
 * @package Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 */

/**
 * This script is a part of ImboClient' test suite. The client drivers use this script when doing
 * actual HTTP requests.
 */

if (isset($_GET['redirect'])) {
    $num = (int) $_GET['redirect'];

    if ($num) {
        $num--;
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?redirect=' . $num;
        header('Location: ' . $url);
        exit;
    }
}

if (isset($_GET['headers'])) {
    $headers = array();

    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) === 'HTTP_') {
            $headers[$key] = $value;
        }
    }

    print(serialize($headers));
    exit;
}

// Sleep some some seconds if specified (to test timeouts)
if (isset($_REQUEST['sleep'])) {
    sleep($_REQUEST['sleep']);
}

// Initialize return data
$data = array(
    'method' => $_SERVER['REQUEST_METHOD'],
);

switch ($data['method']) {
    case 'PUT':
    case 'POST':
        $rawData = file_get_contents('php://input');

        $data['md5'] = md5($rawData);
        $data['data'] = $rawData;
        break;
    case 'GET':
        $data['data'] = $_GET;
        break;
}

if (isset($_GET['clientError'])) {
    header('HTTP/1.0 400 Bad Request');

    if (!isset($_GET['emptyBody'])) {
        echo json_encode(array('error' => array('code' => 400, 'message' => 'bad request')));
    }

    exit;
} else if (isset($_GET['serverError'])) {
    header('HTTP/1.0 500 Internal Server Error');
    if (!isset($_GET['emptyBody'])) {
        echo json_encode(array('error' => array('code' => 500, 'message' => 'internal server error')));
    }

    exit;
}

print(serialize($data));
