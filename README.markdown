# PHP client for Imbo
A PHP client for [Imbo](https://github.com/imbo/imbo).

[![Current build Status](https://secure.travis-ci.org/imbo/imboclient-php.png)](http://travis-ci.org/imbo/imboclient-php)

## Requirements
ImboClient requires a [PSR-0](http://groups.google.com/group/php-standards) compatible autoloader and only works on [PHP-5.3](http://php.net/) or above.

## Installation
ImboClient can be installed using [PEAR](http://pear.php.net/):

```
sudo pear config-set auto_discover 1
sudo pear install --alldeps pear.starzinger.net/ImboClient
```

or with [Composer](http://getcomposer.org/) by specifying `imbo/imboclient` in your dependencies, or by running the following commands:

```
curl -s https://getcomposer.org/installer | php
php composer.phar create-project imbo/imboclient [<dir>] [<version>]
```

You can also download [imboclient.phar](https://github.com/imbo/imboclient-php/raw/master/imboclient.phar) and simply include that file where you want to use ImboClient.

## Usage
### Exceptions
The client throws exceptions whenever an error occurs. There are currently three different exceptions you can expect:

* `ImboClient\Exception\RuntimeException`
* `ImboClient\Exception\InvalidArgumentException`
* `ImboClient\Exception\ServerException`

The server exception is thrown if the Imbo server responds with an error (status code equal to or above 400). The response object is available via the `getResponse()` method on the exception instance. The other two exceptions are thrown if some other error occurs.

These exceptions all implement the `ImboClient\Exception` interface, so if you don't care about the specific error, simply catch `ImboClient\Exception`.

### Add an image
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

$response = $client->addImage('/path/to/image.png'); // Local image
// OR
$response = $client->addImageFromString(file_get_contents('/path/to/image.png'); // In-memory image
// OR
$response = $client->addImageFromUrl('http://example.com/image.png'); // Image from URL
// OR
$imageUrl = $client->getImageUrl('<image identifier>')->resize(200);
$response = $client->addImageFromUrl($imageUrl); // Image from `ImboClient\Url\Image` instance

echo "The image was added! Image identifier: " . $response->getImageIdentifier();
```

### Add an image (with exception handling)
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

try {
    $response = $client->addImage('/path/to/image.png');

    echo "The image was added! Image identifier: " . $response->getImageIdentifier() . PHP_EOL;
} catch (ImboClient\Exception\ServerException $e) {
    echo "An error occured (HTTP " . $e->getCode() . "): " . $e->getMessage() . PHP_EOL;

    // You can also inspect the response from the server
    $response = $e->getResponse();
    $imboErrorCode = $response->getImboErrorCode();

    if ($imboErrorCode === ImboClient\Http\Response\ResponseInterface::IMAGE_ALDREADY_EXISTS) {
        echo "The image already exists on the server" . PHP_EOL;
    }

    // More error codes can be found in ImboClient\Http\Response\ResponseInterface
} catch (ImboClient\Exception $e) {
    echo "An error occured: " . $e->getMessage() . PHP_EOL;
}
```

### Add/edit meta data
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

// Add some meta data to the image
$metadata = array(
    'foo' => 'bar',
    'bar' => 'foo',
);

$imageIdentifier = '<image identifier>';
$response = $client->editMetadata($imageIdentifier, $metadata);
```

### Get meta data
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

$imageIdentifier = '<image identifier>';
$response = $client->getMetadata($imageIdentifier);
```

### Delete an image
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

$imageIdentifier = '<image identifier>';
$response = $client->deleteImage($imageIdentifier);
```

### Delete all meta data attached to an image
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

$imageIdentifier = '<image identifier>';
$response = $client->deleteMetadata($imageIdentifier);
```

### Replace existing meta data attached to an image
```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

$imageIdentifier = '<image identifier>';
$response = $client->replaceMetadata($imageIdentifier, array('key' => 'value'));
```

### Generate Imbo URLs
The client has several methods for fetching URLs to an Imbo installation. The following methods exist:

* `getStatusUrl()` Returns an instance of `ImboClient\Url\Status`.
* `getUserUrl()` Returns an instance of `ImboClient\Url\User`.
* `getImagesUrl()` Returns an instance of `ImboClient\Url\Images`.
* `getImageUrl($imageIdentifier)` Returns an instance of `ImboClient\Url\Image`.
* `getMetadataUrl($imageIdentifier)` Returns an instance of `ImboClient\Url\Metadata`.

These classes implements the `ImboClient\Url\UrlInterface` interface which includes the following methods:

* `getUrl()` Returns the URL as a string.
* `getUrlEncoded()` Returns the URL as a URL-encoded string.
* `reset()` Reset the query parameters added to the URL.
* `addQueryParam()` Add a query parameter to the URL.

When the classes listed above is used in a string context (for instance `print` or `echo`) the `getUrl()` method will be used. All URLs have an access token appended to them that is used by Imbo servers to make sure you have access to the URL you are requesting. The access token is a keyed SHA256 hash using the HMAC method. The key used is the private key given to the client upon instantiation.

#### Image URLs
The `ImboClient\Url\Image` class also implements some other methods that can be used to easily add transformations to the URL (which is only relevant for image URLs). All these methods can be chained and the transformations will be applied to the URL in the chained order.

The `convert()` method is special in that it does not append anything to the URL, except injects an image extension to the image identifier. `convert()` (and `gif()`, `jpg()` and `png()` which proxies to `convert()`) can therefore be added anywhere in the chain.

An example of how we can use the `ImboClient\Url\Image` class to resize an image while maintaining aspect ratio, then adding a border and outputting it in PNG format:

```php
<?php
$client = new ImboClient\Client('http://<hostname>', '<publickey>', '<privatekey>');

$imageIdentifier = '<image identifier>';
$imageUrl = $client->getImageUrl($imageIdentifier);

echo '<img src="' . $imageUrl->maxSize(320, 240)->border('f00baa', 2, 2)->png() . '">';
```

The transformations that can be chained are:

**border()**

Add a border around the image.

* `(string) $color` Color in hexadecimal. Defaults to '000000' (also supports short values like 'f00' ('ff0000')).
* `(int) $width` Width of the border on the left and right sides of the image. Defaults to 1.
* `(int) $height` Height of the border on the top and bottom sides of the image. Defaults to 1.

**canvas()**

Builds a new canvas and allows easy positioning of the original image within it.

* `(int) $width` Width of the new canvas.
* `(int) $height` Height of the new canvas.
* `(string) $mode` Placement mode. 'free' (uses `$x` and `$y`), 'center', 'center-x' (centers horizontally, uses `$y` for vertical placement), 'center-y' (centers vertically, uses `$x` for horizontal placement). Default to 'free'.
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

**desaturate()**

Desaturates the image (essentially grayscales it).

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

**transpose()**

Creates a vertical mirror image by reflecting the pixels around the central x-axis while rotating them 90-degrees.

**transverse()**

Creates a horizontal mirror image by reflecting the pixels around the central y-axis while rotating them 270-degrees.

### Support for multiple hostnames
Following the recommendation of the HTTP 1.1 specification, browsers typically default to two simultaneous requests per hostname. If you wish to generate URLs that point to a range of different hostnames, you can do this by passing an array of URLs to the ImboClient when instantiating:

```php
<?php
$client = new ImboClient\Client(array(
    'http://<url1>',
    'http://<url2>',
    'http://<url3>',
), '<publickey>', '<privatekey>');
```

When using `getImageUrl($imageIdentifier)` and `getMetadataUrl($imageIdentifier)`, the client will pick one of the URLs defined. The same image identifier will result in the same URL, as long as the number of URLs given does not change.

Calls to `getUserUrl()` and `getImagesUrl()` will always use the first URL in the list supplied to the constructor.
