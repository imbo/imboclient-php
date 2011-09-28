# PHP client for imbo
A custom PHP client for [imbo](https://github.com/christeredvartsen/imbo).

## Add an image

    <?php
    require 'ImboClient/Autoload.php';

    $client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

    // Path to local image
    $path = '/path/to/image.png';

    // Add some meta data to the image
    $metadata = array(
        'foo' => 'bar',
        'bar' => 'foo',
    );

    $response = $client->addImage($path, $metadata);

## Get meta data

    <?php
    require 'ImboClient/Autoload.php';

    $client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

    $hash = '<hash>';
    $response = $client->getMetadata($hash);

## Delete an image

    <?php
    require 'ImboClient/Autoload.php';

    $client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

    $hash = '<hash>';
    $response = $client->deleteImage($hash);

## Delete all meta data attached to an image

    <?php
    require 'ImboClient/Autoload.php';

    $client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

    $hash = '<hash>';
    $response = $client->deleteMetadata($hash);
