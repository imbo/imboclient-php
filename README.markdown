# PHP client for imbo
A PHP client for [imbo](https://github.com/christeredvartsen/imbo).

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

## Generate image urls

The client has a method called `getImageUrl($imageIdentifier)` that can be used to fetch an instance of the `ImboClient\ImageUrl\ImageUrl` class. This class has convenience methods for adding transformations to the url. All these methods can be chained and the transformations will be applied to the url in the chaining order. The convert method is special in that it does not append anything to the url, excpect injects an image extension to the image identifier. `convert()`, `gif()`, `jpg()` and `png()` can therefore be added anywhere in the chain.

The class also implements the `__toString()` methods, so treating the object as a string will give you the complete url of the image.

### Methods

#### border()
Add a border around the image.

* `(string) $color` Color in hexadecimal. Defaults to '000000' (also supports short values like 'f00' ('ff0000')).
* `(int) $width` Width of the border on the left and right sides of the image. Defaults to 1.
* `(int) $height` Height of the border on the top and bottoms sides of the image. Defaults to 1.

#### compress()
Compress the image on the fly.

* `(int) $quality` Quality of the resulting image. 100 is maximum quality (lowest compression rate)

#### convert()
Converts the image to another type.

* `(string) $type` The type to convert to. Supported types are: 'gif', 'jpg' and 'png'.

#### gif()
Proxies to `convert('gif')`.

#### jpg()
Proxies to `convert('jpg')`.

#### png()
Proxies to `convert('png')`.

#### crop()
Crop the image.

* `(int) $x` The X coordinate of the cropped region's top left corner.
* `(int) $y` The Y coordinate of the cropped region's top left corner.
* `(int) $width` The width of the crop.
* `(int) $height` The height of the crop.

#### flipHorizontally()
Flip the image horizontally.

#### flipVertically()
Flip the image vertically.

#### resize()
Resize the image. Two parameters are supported and at least one of them must be supplied to apply this transformation.

* `(int) $width` The width of the resulting image in pixels. If not specified the width will be calculated using the same ratio as the original image.
* `(int) $height` The height of the resulting image in pixels. If not specified the height will be calculated using the same ratio as the original image.

#### rotate()
Rotate the image.

* `(int) $angle` The number of degrees to rotate the image.
* `(string) $bg` Background color in hexadecimal. Defaults to '000000' (also supports short values like 'f00' ('ff0000')).

#### thumbnail()
Generate a thumbnail of the image.

* `(int) $width` Width of the thumbnail. Defaults to 50.
* `(int) $height` Height of the thumbnail. Defaults to 50.
* `(string) $fit` Fit style. 'inset' or 'outbound'. Default to 'outbound'.
