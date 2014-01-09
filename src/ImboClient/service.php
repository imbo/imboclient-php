<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

return array(
    'name' => 'Imbo',
    'apiVersion' => '~1.0',
    'description' => 'Imbo\'s API allows clients to add/delete images and add/update/delete metadata attached to these images',
    'operations' => array(
        'GetServerStats' => array(
            'httpMethod' => 'GET',
            'uri' => '/stats.json',
            'summary' => 'Get statistics',
        ),
        'GetServerStatus' => array(
            'httpMethod' => 'GET',
            'uri' => '/status.json',
            'summary' => 'Get status about the Imbo host',
            'responseClass' => 'GetServerStatus',
        ),
        'GetUserInfo' => array(
            'httpMethod' => 'GET',
            'uri' => '/users/{publicKey}.json',
            'summary' => 'Get information about a specific user',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The public key of the user we want information about',
                ),
            ),
            'responseClass' => 'GetUserInfo',
        ),
        'AddImage' => array(
            'httpMethod' => 'POST',
            'uri' => '/users/{publicKey}/images',
            'summary' => 'Add an image',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The owner of the image',
                ),
                'image' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'body',
                    'description' => 'The binary data of the image to add',
                ),
            ),
            'responseClass' => 'AddImage',
        ),
        'DeleteImage' => array(
            'httpMethod' => 'DELETE',
            'uri' => '/users/{publicKey}/images/{imageIdentifier}',
            'summary' => 'Delete an image',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The owner of the image',
                ),
                'imageIdentifier' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The image identifier',
                ),
            ),
            'responseClass' => 'DeleteImage',
        ),
        'GetImageProperties' => array(
            'httpMethod' => 'HEAD',
            'uri' => '/users/{publicKey}/images/{imageIdentifier}',
            'summary' => 'Get properties of an image',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The owner of the image',
                ),
                'imageIdentifier' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The image identifier',
                ),
            ),
            'responseClass' => 'GetImageProperties',
        ),
        'EditMetadata' => array(
            'httpMethod' => 'POST',
            'uri' => '/users/{publicKey}/images/{imageIdentifier}/metadata',
            'summary' => 'Update metadata attached to an image. Supports partial updates',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The owner of the image',
                ),
                'imageIdentifier' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The image identifier',
                ),
                'metadata' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'body',
                    'description' => 'Metadata presented as JSON',
                ),
            ),
            'responseClass' => 'EditMetadata',
        ),
        'GetMetadata' => array(
            'httpMethod' => 'GET',
            'uri' => '/users/{publicKey}/images/{imageIdentifier}/metadata.json',
            'summary' => 'Get metadata attached to an image',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The owner of the image',
                ),
                'imageIdentifier' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The image identifier',
                ),
            ),
        ),
        'ReplaceMetadata' => array(
            'httpMethod' => 'PUT',
            'uri' => '/users/{publicKey}/images/{imageIdentifier}/metadata',
            'summary' => 'Completely replace the metadata attached to an image with new metadata',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The owner of the image',
                ),
                'imageIdentifier' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The image identifier',
                ),
                'metadata' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'body',
                    'description' => 'Metadata presented as JSON',
                ),
            ),
            'responseClass' => 'ReplaceMetadata',
        ),
        'DeleteMetadata' => array(
            'httpMethod' => 'DELETE',
            'uri' => '/users/{publicKey}/images/{imageIdentifier}/metadata',
            'summary' => 'Delete metadata attached to an image',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The owner of the image',
                ),
                'imageIdentifier' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The image identifier',
                ),
            ),
            'responseClass' => 'DeleteMetadata',
        ),
        'GetImages' => array(
            'httpMethod' => 'GET',
            'uri' => '/users/{publicKey}/images.json',
            'summary' => 'Fetch information about images owned by a specific user',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The owner of the images',
                ),
                'page' => array(
                    'type' => 'integer',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Which page to fetch from',
                ),
                'limit' => array(
                    'type' => 'integer',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Limit the number of images returned',
                ),
                'metadata' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Whether or not to include metadata in the response',
                ),
                'from' => array(
                    'type' => 'integer',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Unix timestamp representing the oldest possible image in the set',
                ),
                'to' => array(
                    'type' => 'integer',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Unix timestamp representing the newest possible image in the set',
                ),
                'fields' => array(
                    'type' => 'array',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Array of fields to include in the result set',
                ),
                'sort' => array(
                    'type' => 'array',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Array of fields to sort by',
                ),
                'ids' => array(
                    'type' => 'array',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Array of image identifiers to filter by',
                ),
                'checksums' => array(
                    'type' => 'array',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Array of image checksums to filter by',
                ),
            ),
            'responseClass' => 'GetImages',
        ),
    ),
    'models' => array(
        'GetServerStatus' => array(
            'type' => 'array',
            'properties' => array(
                'status' => array(
                    'location' => 'statusCode',
                    'type' => 'integer',
                ),
                'message' => array(
                    'location' => 'reasonPhrase',
                    'type' => 'string',
                ),
                'date' => array(
                    'location' => 'json',
                    'type' => 'string',
                    'filters' => array(
                        'date_create',
                    ),
                ),
            ),
        ),
        'GetUserInfo' => array(
            'type' => 'array',
            'properties' => array(
                'publicKey' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
                'numImages' => array(
                    'location' => 'json',
                    'type' => 'integer',
                ),
                'lastModified' => array(
                    'location' => 'json',
                    'type' => 'datetime',
                    'filters' => array(
                        'date_create',
                    ),
                ),
            ),
        ),
        'AddImage' => array(
            'type' => 'array',
            'properties' => array(
                'imageIdentifier' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
                'width' => array(
                    'location' => 'json',
                    'type' => 'integer',
                ),
                'height' => array(
                    'location' => 'json',
                    'type' => 'integer',
                ),
                'extension' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
                'status' => array(
                    'location' => 'statusCode',
                    'type' => 'integer',
                ),
            ),
        ),
        'DeleteImage' => array(
            'type' => 'array',
            'properties' => array(
                'imageIdentifier' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
            ),
        ),
        'EditMetadata' => array(
            'type' => 'array',
            'properties' => array(
                'imageIdentifier' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
            ),
        ),
        'ReplaceMetadata' => array(
            'type' => 'array',
            'properties' => array(
                'imageIdentifier' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
            ),
        ),
        'DeleteMetadata' => array(
            'type' => 'array',
            'properties' => array(
                'imageIdentifier' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
            ),
        ),
        'GetImageProperties' => array(
            'type' => 'array',
            'properties' => array(
                'width' => array(
                    'location' => 'header',
                    'type' => 'integer',
                    'sentAs' => 'x-imbo-originalwidth',
                    'filters' => array('intval'),
                ),
                'height' => array(
                    'location' => 'header',
                    'type' => 'integer',
                    'sentAs' => 'x-imbo-originalheight',
                    'filters' => array('intval'),
                ),
                'filesize' => array(
                    'location' => 'header',
                    'type' => 'integer',
                    'sentAs' => 'x-imbo-originalfilesize',
                    'filters' => array('intval'),
                ),
                'extension' => array(
                    'location' => 'header',
                    'type' => 'string',
                    'sentAs' => 'x-imbo-originalextension',
                ),
                'mimetype' => array(
                    'location' => 'header',
                    'type' => 'string',
                    'sentAs' => 'x-imbo-originalmimetype',
                ),
            ),
        ),
        'GetImages' => array(
            'type' => 'array',
            'properties' => array(
                'search' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
                'images' => array(
                    'location' => 'json',
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'added' => array(
                                'location' => 'json',
                                'type' => 'datetime',
                                'filters' => array(
                                    'date_create',
                                ),
                            ),
                            'updated' => array(
                                'location' => 'json',
                                'type' => 'datetime',
                                'filters' => array(
                                    'date_create',
                                ),
                            ),
                            'checksum' => array(
                                'location' => 'json',
                            ),
                            'extension' => array(
                                'location' => 'json',
                            ),
                            'size' => array(
                                'location' => 'json',
                                'type' => 'integer',
                            ),
                            'width' => array(
                                'location' => 'json',
                                'type' => 'integer',
                            ),
                            'height' => array(
                                'location' => 'json',
                                'type' => 'integer',
                            ),
                            'mime' => array(
                                'location' => 'json',
                                'type' => 'string',
                            ),
                            'imageIdentifier' => array(
                                'location' => 'json',
                                'type' => 'string',
                            ),
                            'publicKey' => array(
                                'location' => 'json',
                                'type' => 'string',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
