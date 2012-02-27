# PHP client for imbo
A PHP client for [imbo](https://github.com/imbo/imbo).

[![Current build Status](https://secure.travis-ci.org/imbo/imboclient-php.png)](http://travis-ci.org/imbo/imboclient-php)

## Requirements
ImboClient requires a [PSR-0](http://groups.google.com/group/php-standards) compatible autoloader and only works on [PHP-5.3](http://php.net/) or above.

## Installation
ImboClient can be installed using [PEAR](http://pear.php.net/).

```
sudo pear config-set auto_discover 1
sudo pear install --alldeps pear.starzinger.net/ImboClient
```

## Add an image
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

// Path to local image
$path = '/path/to/image.png';

try {
	$response = $client->addImage($path);

    if ($response->isSuccess()) {
        echo "The image was added! Image identifier: " . $response->getImageIdentifier();
    } else {
        echo "The image was not added!";
    }
} catch (RuntimeException $e) {
    echo "An error occured: " . $e->getMessage();
}

```
## Add/edit meta data
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

// Add some meta data to the image
$metadata = array(
    'foo' => 'bar',
    'bar' => 'foo',
);

$hash = '<hash>';
$response = $client->editMetadata($hash, $metadata);
```
## Get meta data
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

$hash = '<hash>';
$response = $client->getMetadata($hash);
```
## Delete an image
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

$hash = '<hash>';
$response = $client->deleteImage($hash);
```
## Delete all meta data attached to an image
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

$hash = '<hash>';
$response = $client->deleteMetadata($hash);
```
## Generate image urls

The client has a method called `getImageUrl($imageIdentifier)` that can be used to fetch an instance of the `ImboClient\ImageUrl\ImageUrl` class. This class has convenience methods for adding transformations to the url. All these methods can be chained and the transformations will be applied to the url in the chaining order. The `convert()` method is special in that it does not append anything to the url, excpect injects an image extension to the image identifier. `convert()`, `gif()`, `jpg()` and `png()` can therefore be added anywhere in the chain.

The class also includes two methods for fetching a string representation of the URL with the transformations added: `getUrl()` and `getUrlEncoded()`. When the object is used in string context the `getUrl()` method will be used.

### Methods

**border()**

Add a border around the image.

* `(string) $color` Color in hexadecimal. Defaults to '000000' (also supports short values like 'f00' ('ff0000')).
* `(int) $width` Width of the border on the left and right sides of the image. Defaults to 1.
* `(int) $height` Height of the border on the top and bottoms sides of the image. Defaults to 1.

**canvas()**

Builds a new canvas and allows easy positioning of the original image within it.

* `(int) $width` Width of the new canvas.
* `(int) $height` Height of the new canvas.
* `(string) $mode` Placement mode. 'free' (uses $x and $y), 'center', 'center-x' (centers horizontally, uses $y for vertical placement), 'center-y' (centers vertically, uses $x for horizontal placement). Default to 'free'.
* `(int) $x` X coordinate of the placement of the upper left corner of the existing image.
* `(int) $y` Y coordinate of the placement of the upper left corner of the existing image.
* `(string) $bg` Background color of the canvas.

**compress()**

Compress the image on the fly.

* `(int) $quality` Quality of the resulting image. 100 is maximum quality (lowest compression rate)

**convert()**

Converts the image to another type.

* `(string) $type` The type to convert to. Supported types are: 'gif', 'jpg' and 'png'.

**crop()**

Crop the image.

* `(int) $x` The X coordinate of the cropped region's top left corner.
* `(int) $y` The Y coordinate of the cropped region's top left corner.
* `(int) $width` The width of the crop.
* `(int) $height` The height of the crop.

**flipHorizontally()**

Flip the image horizontally.

**flipVertically()**

Flip the image vertically.

**gif()**

Proxies to `convert('gif')`.

**jpg()**

Proxies to `convert('jpg')`.

**maxSize()**

Resize the image using the original aspect ratio.

* `(int) width` The max width of the resulting image in pixels. If not specified the width will be calculated using the same ratio as the original image.
* `(int) height` The max height of the resulting image in pixels. If not specified the height will be calculated using the same ratio as the original image.

**png()**

Proxies to `convert('png')`.

**resize()**

Resize the image. Two parameters are supported and at least one of them must be supplied to apply this transformation.

* `(int) $width` The width of the resulting image in pixels. If not specified the width will be calculated using the same ratio as the original image.
* `(int) $height` The height of the resulting image in pixels. If not specified the height will be calculated using the same ratio as the original image.

**rotate()**

Rotate the image.

* `(int) $angle` The number of degrees to rotate the image.
* `(string) $bg` Background color in hexadecimal. Defaults to '000000' (also supports short values like 'f00' ('ff0000')).

**thumbnail()**

Generate a thumbnail of the image.

* `(int) $width` Width of the thumbnail. Defaults to 50.
* `(int) $height` Height of the thumbnail. Defaults to 50.
* `(string) $fit` Fit style. 'inset' or 'outbound'. Default to 'outbound'.

## Multiple URL support

Following the recommendation of the HTTP 1.1 specification, browsers typically default to two simultaneous requests per hostname. If you wish to generate URLs that point to a range of different hostnames, you can do this by passing an array of URLs to the ImboClient when instantiating:

```php
<?php
$client = new ImboClient\Client(array(
    'http://<url1>',
    'http://<url2>',
    'http://<url3>',
), '<publickey>', '<privatekey>');
```

When using `getImageUrl($imageIdentifier)`, the client will pick one of the URLs defined. The same image identifier will result in the URL, as long as the number of URLs given does not change.

Calls to `getUserUrl()` and `getImagesUrl()` will always use the first URL in the list supplied to the constructor.
