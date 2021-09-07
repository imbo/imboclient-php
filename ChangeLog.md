Changelog for ImboClient
========================

ImboClient-2.0.3
----------------
__2016-03-17__

* #109: Don't allow generation of image URL's with no image identifier (Christer Edvartsen)

ImboClient-2.0.2
----------------
__2015-12-06__

Bug fix: Lock `symfony/yaml` dependency to specific version to prevent errors on PHP 5.3/5.4.

ImboClient-2.0.1
----------------
__2015-12-06__

Bug fix: Fix normalizing of image URLs causing incorrect access tokens.

ImboClient-2.0.0
----------------
__2015-12-05__

This version of the client includes one backward compatibility break:

When constructing ImageUrl's using `ImageUrl::factory()`, it will be populated with the transformations and extension of the original URL. If anyone relied on the constructed image url to be cleared of any transformations, you will now have to call `reset()` on the image url instance returned, before applying any other transformations.

ImboClient-1.3.0
----------------
__2015-12-02__

* #104: Added support for Imbo 2.x:
    * New `user` parameter added to client constructor/factory
    * Support image URLs with image identifiers outside of hex-range, and with lengths down to 1 character
    * Add methods for handling access control - resource groups, access control rules and public keys, including documentation for these
    * Add new transformations to the ImageUrl-class (`blur`, `contrast`, `drawPois`, `level`, `sharpen`, `smartSize`, `vignette`)

Bug fixes:

* #101: Fix bug where generateShortUrl would not sign requests (J5lx)

ImboClient-1.2.0
----------------
__2014-04-08__

* #100: Added support for the histogram transformation added to Imbo-1.2.0
* #99: Updated responses from the server when writing to the metadata resource (edit, update and delete)
* #93: Updated how the client adds short image URLs with regards to changes made to the Imbo server
* #92: Added support for filtering images on the originalChecksum

Bug fixes:

* #91: Image extension is replicated when a URL is converted to a string more than once
* #90: Imbo\Http\ImageUrl instances does not support being converted to strings more than once

ImboClient-1.1.0
----------------
__2014-02-13__

* #89: Added support for crop modes (available in Imbo >= 1.1.0)
* Depend on guzzle/guzzle ~3.8 instead of ~3.0, and phpunit/phpunit ~3.7 instead of ~3.0
* The addImageFromUrl() method will no longer throw an InvalidArgumentException if the client fails to fetch the image. Instead Guzzle will throw a fitting exception.

ImboClient-1.0.1
----------------
__2014-02-06__

* Lowered the requirement of PHP from 5.4 to 5.3.3.
* Fixed some issues in the documentation regarding how to create an instance of the client

ImboClient-1.0.0
----------------
__2014-01-30__

* Rewrote the client to use Guzzle. Some BC issues, see docs for more information

ImboClient-0.8.1
----------------
__2013-05-12__

* Fixed issue #78: Issue when response body is not JSON-serialized
* Add support for sepia transformation (Espen Hovlandsdal)

ImboClient-0.8.0
----------------
__2013-01-12__

* Fixed issue #74: Added method for fetching user information

ImboClient-0.7.0
----------------
__2012-11-26__

* Pull request #72: Image class should expect dates as strings (Espen Hovlandsdal)
* Fixed issue #68: SSL issues because some options are set when they are empty
* Pull request #66: getImageProperties should return more information
* Pull request #65: Implemented the imageIdentifierExists()-method.
* Pull request #63: Add support for desaturate transformation (Espen Hovlandsdal)
* Fixed issue #61: The response can not be used in a string context of the body is empty
* Fixed issue #60: getMetadata() returns the response and not the metadata

ImboClient-0.6.1
----------------
__2012-09-19__

* Fixed issue #55: imageExists() triggers exception if image does not exist

ImboClient-0.6.0
----------------
__2012-08-31__

* Fixed issue #52: Make sure that requests does not include headers from previous requests
* Fixed issue #46: Automatically add "http://" to the server URL(s) is not specified
* Fixed issue #45: Drivers should throw an exception if the response contains an error
* Renamed cURL driver to `ImboClient\Driver\cURL`

ImboClient-0.5.2
----------------
__2012-06-21__

* Fixed issue #43: Fixed a bug where CURLOPT\_FOLLOWLOCATION would cause the response to include several HTTP header blocks when hitting a 3xx-response from the server (Espen Hovlandsdal)

ImboClient-0.5.1
----------------
__2012-06-18__

* Fixed composer.json

ImboClient-0.5.0
----------------
__2012-06-18__

* Added generation of imboclient.phar in the release process
* Added composer.json so ImboClient can be installed with Composer (http://getcomposer.org/)
* Added support for the status resource on the server
* Pull request #35: Added support for transpose and transverse-transformations. (Espen Hovlandsdal)
* cURL driver now accepts cURL options

ImboClient-0.4.2
----------------
__2012-03-22__

* Fixed more issues regarding the access token generation.

ImboClient-0.4.1
----------------
__2012-03-21__

* Fixed bug with query params not being included when generating the access token. This bug broke get `getImages()` method in the client.

ImboClient-0.4.0
----------------
__2012-03-21__

This version of the client includes some backward compatibility breaks. The client now returns different implementations of `ImboClient\Url\UrlInterface` for the following methods in the client:

* `getUserUrl()`
* `getImagesUrl()`
* `getImageUrl()`
* `getMetadataUrl()`

All implementations can be used as strings so if your code mostly prints these out you are good to go.

The main reason for the BC breaks is a new feature that is to be implemented on the server that requires access tokens for all URLs. The way ImboClient-0.3.0 handles URLs made this feature less clean to implement. All URLs that is generated by the client now includes this access token, which is a keyed SHA256 hash using the private key.

Other changes include a change to the namespace of the class used when querying Imbo for images as well as the image instances returned from this query. They have both been changed from `ImboClient\ImagesQuery` to `ImboClient\Url\Images`.

ImboClient-0.3.0
----------------
__2012-03-08__

* Fixed issue #19: Method for adding images via URLs
* Fixed issue #17: Allow passing cURL-driver options related to SSL
* Pull request #18: Added maxSize-transformation to clients ImageUrl-interface (Espen Hovlandsdal)
* Fixed issue #16: ImboClient does not remove an explicitly set port 443 from the URL when using https://

ImboClient-0.2.0
----------------
__2012-02-22__

* Pull request #15: Added support for multiple hostnames (Espen Hovlandsdal)
* Fixed issue #14: Provide a simple way to fetch the error from a failed request
* Fixed issue #13: Implemented support for adding in-memory images to the server

ImboClient-0.1.1
----------------
__2012-02-13__

* Pull request #12: Binary data from requests are (possibly) broken (Espen Hovlandsdal)
* Pull request #11: Added getImageData() and getImageDataFromUrl()-methods (Espen Hovlandsdal)

ImboClient-0.1.0
----------------
__2012-02-09__

* Initial release
