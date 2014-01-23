ImboClient - PHP
================

This is the official PHP-based client for `Imbo <https://github.com/imbo/imbo>`_ servers.

Requirements
------------

The client requires `PHP >= 5.4 <http://php.net/>`_.

Installation
------------

ImboClient can be installed using `Composer <http://getcomposer.org/>`_ by requiring ``imbo/imboclient`` in your ``composer.json`` file, or by running the following commands::

    curl -s https://getcomposer.org/installer | php
    php composer.phar create-project imbo/imboclient [<dir>] [<version>]

Available versions can be located at `packagist <https://packagist.org/packages/imbo/imboclient>`_.

Usage
-----

Below you will find documentation covering most features of the client.

.. contents::
    :local:

Instantiating the client
++++++++++++++++++++++++

This can be achieved in two different ways:

1) Use the factory:

.. code-block:: php

    $client = ImboClient\ImboClient::factory(array('http://imbo.example.com'), 'user', 'private key');

2) Use the constructor:

.. code-block:: php

    $client = new ImboClient\ImboClient('http://imbo.example.com', 'user', 'private key');

The main difference is that the first argument to the factory method requires you to specify the host name(s) of the Imbo server(s) as an array, while the constructor requires you to pass a string. If you want to use the example number 2 above, and still want to use multiple host names you can use the ``setServerUrls`` method:

.. code-block:: php

    $client->setServerUrls(array(
        'http//imbo1.example.com',
        'http//imbo2.example.com',
        'http//imbo3.example.com',
    ));

If you use multiple URL's when instantiating the client it will choose different image URL's based on the image identifier and the number of available host names. If you have a site which includes a lot of ``<img>`` tags against an Imbo server, using multiple hosts might speed up the loading time for your users. If you don't change the amount of server URL's the client will always pick the same host name given the same image identifier.

Error handling
++++++++++++++

Most methods will throw a ``Guzzle\Common\Exception\GuzzleException`` exception if the server responds with an error (as in HTTP 4** or 5**). Some methods might also throw an ``InvalidArgumentException`` exception of the provided parameter to a method is invalid (for instance if you try to add an image and provide a local path to a file that does not exist). Remember to use ``try/catch`` if you want to handle these errors gracefully.

Get server status
+++++++++++++++++

If you want to get the server status, you can use the ``getServerStatus`` method:

.. code-block:: php

    $status = $client->getServerStatus();

The ``$status`` value above can be used as an associative array, and includes the following elements:

``(boolean) database``
    Whether or not the configured database works as expected on the server.

``(boolean) storage``
    Whether or not the configured storage works as expected on the server.

``(int) status``
    The HTTP status code.

``(string) message``
    The HTTP response reason phrase.

Get server statistics
+++++++++++++++++++++

If you have access to the server statistics and want to fetch these, you can use the ``getServerStats`` method:

.. code-block:: php

    $stats = $client->getServerStats();

The return value from this method can be used as an associative array, and includes the following elements:

``(array) users``
    An array of users where the keys are user names (public keys) and values are arrays with the following elements:

    * ``(int) numImages``: Number of images owned by this user
    * ``(int) numBytes``: Number of bytes stored by this user

``(array) total``
    An array with aggregated values. The array includes the following elements:

    * ``(int) numImages``: The number of images on the server
    * ``(int) numUsers``: The number of users on the server
    * ``(int) numBytes``: The number of bytes stored on the server

``(array) custom``
    If the server has configured any custom statistics, these are available in this element.

Get user info
+++++++++++++

Get some information about the user configured with the client:

.. code-block:: php

    $info = $client->getUserInfo();

The value returned from the ``getUserInfo`` method includes the following elements:

``(string) publicKey``
    The public key of the user (the same as the one used when instantiating the client).

``(int) numImages``
    The number of images owned by the user.

``(DateTime) lastModified``
    A ``DateTime`` instance representing when the user last modified any data on the server.

Add an image
++++++++++++

The first thing you might want to do is to start adding images. This can be done in several ways:

1) Add an image from a local path:

.. code-block:: php

    $response = $client->addImage('/path/to/image.jpg');

2) Add an image from a URL:

.. code-block:: php

    $response = $client->addImageFromUrl('http://example.com/some/image.jpg');

3) Add an in-memory image:

.. code-block:: php

    $response = $client->addImageFromString(file_get_contents('/path/to/image.jpg'));

The ``$response`` returned from these methods holds the resulting image identifier of the image, and can be fetched by using the response as an associative array:

.. code-block:: php

    echo 'Image added, identifier: ' . $response['imageIdentifier'];

This is the identifier you will use when generating URL's to the image later on. The response also has some other information that you might find useful:

``(string) imageIdentifier``
    As mentioned above, the ID of the added image.

``(int) width``
    The width of the added image.

``(int) height``
    The height of the added image.

``(string) extension``
    The extension of the added image.

``(int) status``
    The HTTP status of the response from the server. Should be ``200`` or ``201``.

The ``width`` and ``height`` can differ from the original image if the server has added event listeners that might change incoming images. Some changes that might occur is auto rotating based on EXIF-data embedded into the image, and if a max image size is being enforced by the server.

Get image properties
++++++++++++++++++++

You can fetch properties of the image by using the ``getImageProperties`` method, specifying the image identifier of an image:

.. code-block:: php

    $properties = $client->getImageProperties('image identifier');

The return value can be used as an associative array, and contains the following elements:

``(int) width``
    The width of the image in pixels.

``(int) height``
    The height of the image in pixels.

``(int) filesize``
    The file size of the image in bytes.

``(string) extension``
    The extension of the image.

``(string) mimetype``
    The mime type of the image.

Delete an image
+++++++++++++++

If you want to delete an image from the server, you can use the ``deleteImage`` method:

.. code-block:: php

    $response = $client->deleteImage('identifier');

where ``'identifier'`` is the value of the ``imageIdentifier`` key of the response returned when adding images.

Check for the existence of images on the server
+++++++++++++++++++++++++++++++++++++++++++++++

If you want to see if a local image exists on the server, use the ``imageExists($path)`` method:

.. code-block:: php

    $path = '/path/to/image.jpg';
    $exists = $client->imageExists($path);

    echo '"' . $path . '" ' . ($exists ? 'exists' : 'does not exist') . ' on the server.';

You can also check for the existence of an image identifier on the server by using the ``imageIdentifierExists($imageIdentifier)`` method.

Get the number of added images
++++++++++++++++++++++++++++++

If you want to fetch the number of images owned by the current user you can use the ``getNumImages`` methods:

.. code-block:: php

    echo 'The user "' . $client->getPublicKey() . '" has ' . $client->getNumImages() . ' images.';

Get the binary image data
+++++++++++++++++++++++++

If you want to fetch the binary data of an image as a string you can use ``getImageData($imageIdentifier)``. If you have an instance of an image URL you can use the ``getImageDataFromUrl(ImboClient\Http\ImageUrl $imageUrl)`` method:

.. code-block:: php

    $imageData = $client->getImageData($imageIdentifier);

    // or

    $imageData = $client->getImagedataFromUrl($client->getImageUrl($imageIdentifier)->thumbnail()->border());

You can read more about the image URL's in the :ref:`imbo-urls` section.

Search for images
+++++++++++++++++

The client also let's you search for images on the server. This is done via the ``getImages`` method:

.. code-block:: php

    $collection = $client->getImages();

    echo '<h1>Images on the server:</h1>';
    echo '<ul>';

    foreach ($collection['images'] as $image) {
        echo '<li>' . $image['imageIdentifier'] . '</li>';
    }

    echo '</ul>';

The ``$collection`` variable returned from the ``getImages`` methods has two elements: ``search`` and ``images``. ``search`` is an array related to pagination and holds information about the images returned by your query:

``(int) hits``
    The number of hits from your query.

``(int) page``
    The current page.

``(int) limit``
    Limit the number of images per page.

``(int) count``
    The number of images currently on the page.

and the ``images`` element is a traversable where each element represents an image. Each image is an associative array which includes the following elements:

* ``added``
* ``updated``
* ``checksum``
* ``extension``
* ``size``
* ``width``
* ``height``
* ``mime``
* ``imageIdentifier``
* ``publicKey``
* ``metadata`` (only if the query explicitly enabled metadata in the response, which is off by default).

Some of these elements might not be available if the query excludes some fields (more on that below).

The ``getImages`` method can also take a parameter which specifies a query to execute. The parameter is an instance of the ``ImboClient\ImagesQuery`` class. This class has a set of methods that can be used to customize your query. All methods can be chained when used with a parameter (when setting a value). If you skip the parameter, the methods will return the current value instead:

``page($page = null)``
    Set or get the ``page`` value. Defaults to ``1``.

``limit($limit = null)``
    Set or get the ``limit`` value. Defaults to ``20``.

``metadata($metadata = null)``
    Set to true to return metadata attached to the images. Defaults to ``false``. Setting this to ``true`` will make the client include the ``metadata`` element mentioned above in the images in the collection.

``from($from = null)``
    Specify a `Unix timestamp <http://en.wikipedia.org/wiki/Unix_timestamp>`_ which represents the oldest image you want returned in the collection. Defaults to ``null``.

``to($to = null)``
    Specify a Unix timestamp which represents the newest image you want returned in the collection. Defaults to ``null``.

``fields(array $fields = null)``
    Specify which fields should be available per image in the ``images`` element of the response. Defaults to ``null`` (all fields). The fields to include are mentioned above.

    .. note:: If you want to include metadata in the response, remember to include ``metadata`` in the set of fields, **if** you specify custom fields.

``sort(array $sort = null)``
    Specify which field(s) to sort by. Defaults to ``date:desc``. All fields mentioned above can be sorted by, and they all support ``asc`` and ``desc``. If you don't specify a sort order ``asc`` will be used.

``ids(array $ids = null)``
    Only include these image identifiers in the collection. Defaults to ``null``.

``checksums(array $checksums = null)``
    Only include these MD5 checksums in the collection. Defaults to ``null``.

Here are some examples of how to use the query object:

1) Fetch (at most) 10 images added within the last 24 hours, sorted by the image byte size (ascending) and then the width of the image (descending):

.. code-block:: php

    $current = time();
    $query = new ImboClient\ImagesQuery();
    $query->limit(10)->from($current - 3600 * 24)->sort(array('size', 'width:desc'));

    $collection = $client->getImages($query);

2) Include metadata in the response:

.. code-block:: php

    $query = new ImboClient\ImagesQuery();
    $query->metadata(true);

    $collection = $client->getImages($query);

3) Only fetch the ``width`` and ``height`` fields on a set of images:

.. code-block:: php

    $query = new ImboClient\ImagesQuery();
    $query->ids(array('id1', 'id2', 'id3'))->fields(array('width', 'height'));

    $collection = $client->getImages($query);

If you want to return metadata, and happen to specify custom fields you will need to explicitly add the ``metadata`` field. If you don't use the ``fields`` method this is not necessary:

.. code-block:: php

    $query->metadata(true)->fields(array('size')); // Does include the metadata field
    $query->metadata(true)->fields(array('size', 'metadata')); // Includes the size and metadata fields
    $query->metadata(true); // Includes all fields, including metadata
    $query->metadata(false); // Exclude the metadata field (default behaviour)

Get metadata
++++++++++++

Images in Imbo can have metadata attached to them. If you want to fetch this data you can use the ``getMetadata`` method:

.. code-block:: php

    $metadata = $client->getMetadata('image identifier');

    echo '<dl>';

    foreach ($metadata as $key => $value) {
        echo '<dt>' . $key . '</dt>';
        echo '<dd>' . $value . '</dd>';
    }

    echo '</dl>';

Update metadata
+++++++++++++++

If you have added an image and want to edit its metadata you can use the ``editMetadata`` method:

.. code-block:: php

    $response = $client->editMetadata('image identifier', array(
        'key' => 'value',
        'other key' => 'other value',
    ));

This method will partially update existing metadata.

Replace metadata
++++++++++++++++

If you want to replace all existing metadata with something else you can use the ``replaceMetadata`` method:

.. code-block:: php

    $response = $client->replaceMetadata('image identifier', array(
        'key' => 'value',
        'other key' => 'other value',
    ));

This will first remove existing (if any) metadata, and add the metadata specified as the second parameter.

Delete metadata
+++++++++++++++

If you want to remove all metadata attached to an image you can use the ``deleteMetadata`` method:

.. code-block:: php

    $response = $client->deleteMetadata('image identifier');

.. _imbo-urls:

Imbo URL's
++++++++++

Imbo uses access tokens in the URL's to prevent `DoS attacks <http://en.wikipedia.org/wiki/DoS>`_, and the client includes functionality that does this automatically:

``getStatusUrl``
    Fetch a URL to the status endpoint.

``getStatsUrl``
    Fetch a URL to the stats endpoint.

``getUserUrl``
    Fetch a URL to the user information of the current user (specified by setting the correct public key when instantiating the client)``.

``getImagesUrl``
    Fetch a URL to the images endpoint.

``getImageUrl($imageIdentifier)``
    Fetch a URL to a specific image.

``getMetadataUrl($imageIdentifier)``
    Fetch a URL to the metadata of a specific image.

``getShortUrl(ImboClient\Http\ImageUrl $imageUrl, $asString = false)``
    Fetch the short URL to an image (with optional image transformations added).

All these methods return instances of different classes, and all can be used in string context to get the URL with the access token added. The instance returned from the ``getImageUrl`` is somewhat special since it will let you chain a set of transformations before generating the URL as a string:

.. code-block:: php

    $imageUrl = $client->getImageUrl('image identifier');
    $imageUrl->thumbnail()->border()->jpg();

    echo '<img src="' . $imageUrl . '">';

The available transformation methods are:

* ``autoRotate()``
* ``border($color = '000000', $width = 1, $height = 1, $mode = 'outbound')``
* ``canvas($width, $height, $mode = null, $x = null, $y = null, $bg = null)``
* ``compress($level = 75)``
* ``crop($x, $y, $width, $height)``
* ``desaturate()``
* ``flipHorizontally()``
* ``flipVertically()``
* ``maxSize($maxWidth = null, $maxHeight = null)``
* ``progressive()``
* ``resize($width = null, $height = null)``
* ``rotate($angle, $bg = '000000')``
* ``sepia($threshold = 80)``
* ``strip()``
* ``thumbnail($width = 50, $height = 50, $fit = 'outbound')``
* ``transpose()``
* ``transverse()``
* ``watermark($img = null, $width = null, $height = null, $position = 'top-left', $x = 0, $y = 0)``

Please refer to the `server documentation <http://docs.imbo-project.org/>`_ for details about the image transformations.

There are also some other methods available:

``addTransformation($transformation)``
    Can be used to add a custom transformation (that needs to be available on the server):

    .. code-block:: php

        $url->addTransformation('foobar'); // results in t[]=foobar being added to the URL

``convert($type)``
    Convert the image to one of the supported types:

    * ``jpg``
    * ``gif``
    * ``png``

``gif()``
    Proxies to ``convert('gif')``.

``jpg()``
    Proxies to ``convert('jpg')``.

``png()``
    Proxies to ``convert('png')``.

``reset()``
    Removes all transformations added to the URL instance.

The methods related to the image type (``convert`` and the proxy methods) can be added anywhere in the chain. Otherwise all transformations will be applied to the image in the same order as they appear in the chain.
