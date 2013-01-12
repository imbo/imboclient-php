<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
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
        echo json_encode(array('error' => array('code' => 400, 'message' => 'Bad Request')));
    }

    exit;
} else if (isset($_GET['serverError'])) {
    header('HTTP/1.0 500 Internal Server Error');
    if (!isset($_GET['emptyBody'])) {
        echo json_encode(array('error' => array('code' => 500, 'message' => 'Internal Server Error')));
    }

    exit;
}

print(serialize($data));
