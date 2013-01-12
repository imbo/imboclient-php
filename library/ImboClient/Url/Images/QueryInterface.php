<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Url\Images;

/**
 * Images query interface
 *
 * @package ImboClient\Interfaces
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface QueryInterface {
    /**
     * Set or get the page property
     *
     * @param int $page Give this a value to set the page property
     * @return int|QueryInterface
     */
    function page($page = null);

    /**
     * Set or get the limit property
     *
     * @param int $limit Give this a value to set the limit property
     * @return int|QueryInterface
     */
    function limit($limit = null);

    /**
     * Set or get the returnMetadata flag
     *
     * @param boolean $returnMetadata Give this a value to set the returnMetadata flag
     * @return boolean|QueryInterface
     */
    function returnMetadata($returnMetadata = null);

    /**
     * Set or get the metadataQuery property
     *
     * @param array $metadataQuery Give this a value to set the property
     * @return array|QueryInterface
     */
    function metadataQuery(array $metadataQuery = null);

    /**
     * Set or get the from attribute
     *
     * @param int $from Give this a value to set the from property
     * @return int|QueryInterface
     */
    function from($from = null);

    /**
     * Set or get the to attribute
     *
     * @param int $to Give this a value to set the to property
     * @return int|QueryInterface
     */
    function to($to = null);
}
