<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

// Reused in various list resources
$search = array(
    'location' => 'json',
    'type' => 'object',
    'properties' => array(
        'limit' => array(
            'location' => 'json',
            'type' => 'integer',
        ),
        'page' => array(
            'location' => 'json',
            'type' => 'integer',
        ),
        'hits' => array(
            'location' => 'json',
            'type' => 'integer',
        ),
        'count' => array(
            'location' => 'json',
            'type' => 'integer',
        )
    )
);

return array(
    'name' => 'Imbo',
    'apiVersion' => '1.0',
    'description' => 'Imbo\'s API allows clients to add/delete images and add/update/delete metadata attached to these images',
    'operations' => array(
        'GetServerStats' => array(
            'httpMethod' => 'GET',
            'uri' => 'stats.json',
            'summary' => 'Get statistics',
        ),
        'GetServerStatus' => array(
            'httpMethod' => 'GET',
            'uri' => 'status.json',
            'summary' => 'Get status about the Imbo host',
            'responseClass' => 'GetServerStatus',
        ),
        'GetUserInfo' => array(
            'httpMethod' => 'GET',
            'uri' => 'users/{user}.json',
            'summary' => 'Get information about a specific user',
            'parameters' => array(
                'user' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The user we want information about',
                ),
            ),
            'responseClass' => 'GetUserInfo',
        ),
        'AddImage' => array(
            'httpMethod' => 'POST',
            'uri' => 'users/{user}/images',
            'summary' => 'Add an image',
            'parameters' => array(
                'user' => array(
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
            'uri' => 'users/{user}/images/{imageIdentifier}',
            'summary' => 'Delete an image',
            'parameters' => array(
                'user' => array(
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
            'uri' => 'users/{user}/images/{imageIdentifier}',
            'summary' => 'Get properties of an image',
            'parameters' => array(
                'user' => array(
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
            'uri' => 'users/{user}/images/{imageIdentifier}/metadata',
            'summary' => 'Update metadata attached to an image. Supports partial updates',
            'parameters' => array(
                'user' => array(
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
        ),
        'GetMetadata' => array(
            'httpMethod' => 'GET',
            'uri' => 'users/{user}/images/{imageIdentifier}/metadata.json',
            'summary' => 'Get metadata attached to an image',
            'parameters' => array(
                'user' => array(
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
            'uri' => 'users/{user}/images/{imageIdentifier}/metadata',
            'summary' => 'Completely replace the metadata attached to an image with new metadata',
            'parameters' => array(
                'user' => array(
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
        ),
        'DeleteMetadata' => array(
            'httpMethod' => 'DELETE',
            'uri' => 'users/{user}/images/{imageIdentifier}/metadata',
            'summary' => 'Delete metadata attached to an image',
            'parameters' => array(
                'user' => array(
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
        'GetImages' => array(
            'httpMethod' => 'GET',
            'uri' => 'users/{user}/images.json',
            'summary' => 'Fetch information about images owned by a specific user',
            'parameters' => array(
                'user' => array(
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
                'originalChecksums' => array(
                    'type' => 'array',
                    'required' => false,
                    'location' => 'query',
                    'description' => 'Array of original image checksums to filter by',
                ),
            ),
            'responseClass' => 'GetImages',
        ),
        'GenerateShortUrl' => array(
            'httpMethod' => 'POST',
            'uri' => 'users/{user}/images/{imageIdentifier}/shorturls',
            'summary' => 'Create a short URL to an image with a set of image transformations applied to it',
            'parameters' => array(
                'user' => array(
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
                'params' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'body',
                    'description' => 'Parameters for the short URL represented as a JSON-encoded object',
                ),
            ),
            'responseClass' => 'GenerateShortUrl',
        ),
        'GetResourceGroups' => array(
            'httpMethod' => 'GET',
            'uri' => 'groups.json',
            'summary' => 'Fetch available resource groups',
            'parameters' => array(
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
                    'description' => 'Limit the number of groups returned',
                ),
            ),
            'responseClass' => 'GetResourceGroups',
        ),
        'GetResourceGroup' => array(
            'httpMethod' => 'GET',
            'uri' => 'groups/{groupName}.json',
            'summary' => 'Fetch a specific resource group',
            'parameters' => array(
                'groupName' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the resource group',
                ),
            ),
            'responseClass' => 'GetResourceGroup',
        ),
        'GetResourceGroup' => array(
            'httpMethod' => 'GET',
            'uri' => 'groups/{groupName}.json',
            'summary' => 'Fetch a specific resource group',
            'parameters' => array(
                'groupName' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the resource group',
                ),
            ),
            'responseClass' => 'GetResourceGroup',
        ),
        'EditResourceGroup' => array(
            'httpMethod' => 'PUT',
            'uri' => 'groups/{groupName}',
            'summary' => 'Edit or add a resource group',
            'parameters' => array(
                'groupName' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the group to edit/create',
                ),
                'resources' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'body',
                    'description' => 'The resources the group should have access to',
                ),
            ),
        ),
        'DeleteResourceGroup' => array(
            'httpMethod' => 'DELETE',
            'uri' => 'groups/{groupName}',
            'summary' => 'Delete a resource group',
            'parameters' => array(
                'groupName' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the group to delete',
                ),
            ),
        ),
        'EditPublicKey' => array(
            'httpMethod' => 'PUT',
            'uri' => 'keys/{publicKey}',
            'summary' => 'Edit or add a public/private key pair',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the public key to edit/create',
                ),
                'properties' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'body',
                    'description' => 'The properties for this public key',
                ),
            ),
        ),
        'DeletePublicKey' => array(
            'httpMethod' => 'DELETE',
            'uri' => 'keys/{publicKey}',
            'summary' => 'Delete a public key',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the public key to delete',
                ),
            ),
        ),
        'GetAccessControlRules' => array(
            'httpMethod' => 'GET',
            'uri' => 'keys/{publicKey}/access.json',
            'summary' => 'Fetch access control rules for the given public key',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the public key to delete',
                ),
            ),
            'responseClass' => 'GetAccessControlRules',
        ),
        'GetAccessControlRule' => array(
            'httpMethod' => 'GET',
            'uri' => 'keys/{publicKey}/access/{id}.json',
            'summary' => 'Fetch a given access control rule for the given public key',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the public key to retrieve the rule from',
                ),
                'id' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The id of the rule to retrieve',
                ),
            ),
            'responseClass' => 'GetAccessControlRule',
        ),
        'AddAccessControlRules' => array(
            'httpMethod' => 'POST',
            'uri' => 'keys/{publicKey}/access',
            'summary' => 'Add access control rules to the given public key',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the public key to add rules to',
                ),
                'rules' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'body',
                    'description' => 'Access control rules represented as JSON',
                ),
            ),
        ),

        'DeleteAccessControlRule' => array(
            'httpMethod' => 'DELETE',
            'uri' => 'keys/{publicKey}/access/{id}',
            'summary' => 'Delete an access control rule',
            'parameters' => array(
                'publicKey' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'The name of the public key to delete',
                ),
                'id' => array(
                    'type' => 'string',
                    'required' => true,
                    'location' => 'uri',
                    'description' => 'ID of the rule to delete',
                )
            ),
        ),
    ),
    'models' => array(
        'GetServerStatus' => array(
            'type' => 'object',
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
            'type' => 'object',
            'properties' => array(
                'publicKey' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
                'user' => array(
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
            'type' => 'object',
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
            'type' => 'object',
            'properties' => array(
                'imageIdentifier' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
                'status' => array(
                    'location' => 'statusCode',
                    'type' => 'integer',
                ),
            ),
        ),
        'GetImageProperties' => array(
            'type' => 'object',
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
            'type' => 'object',
            'properties' => array(
                'search' => $search,
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
                            'originalChecksum' => array(
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
                            'user' => array(
                                'location' => 'json',
                                'type' => 'string',
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'GenerateShortUrl' => array(
            'type' => 'object',
            'properties' => array(
                'id' => array(
                    'location' => 'json',
                    'type' => 'string',
                ),
                'status' => array(
                    'location' => 'statusCode',
                    'type' => 'integer',
                ),
            ),
        ),
        'GetResourceGroups' => array(
            'type' => 'object',
            'properties' => array(
                'search' => $search,
                'groups' => array(
                    'location' => 'json',
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'name' => array(
                                'location' => 'json',
                            ),
                            'resources' => array(
                                'location' => 'json',
                                'type' => 'array',
                                'items' => array(
                                    'type' => 'string'
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'GetResourceGroup' => array(
            'type' => 'object',
            'properties' => array(
                'resources' => array(
                    'location' => 'json',
                    'type' => 'array',
                    'items' => array(
                        'type' => 'string'
                    ),
                ),
            ),
        ),
        'GetAccessControlRules' => array(
            'type' => 'array',
            'additionalProperties' => array(
                'location' => 'json'
            ),
        ),
        'GetAccessControlRule' => array(
            'type' => 'object',
            'properties' => array(
                'id' => array(
                    'location' => 'json',
                    'required' => true,
                    'type' => 'string',
                ),
                'resources' => array(
                    'type' => 'array',
                    'location' => 'json',
                    'items' => array(
                        'type' => 'string'
                    )
                ),
                'group' => array(
                    'type' => 'string',
                    'require' => false,
                    'location' => 'json',
                ),
                'users' => array(
                    'type' => array('string', 'array'),
                    'location' => 'json',
                    'items' => array('type' => 'string')
                )
            )
        ),
        'AddAccessControlRules' => array(
            'type' => 'object',
            'properties' => array(),
        )
    ),
);
