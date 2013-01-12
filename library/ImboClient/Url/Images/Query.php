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
 * Query object for the images resource
 *
 * @package ImboClient\Urls
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Query implements QueryInterface {
    /**
     * The page to get
     *
     * @var int
     */
    private $page = 1;

    /**
     * Number of images to get
     *
     * @var int
     */
    private $limit = 20;

    /**
     * Return metadata or not
     *
     * @var boolean
     */
    private $returnMetadata = false;

    /**
     * Metadata query
     *
     * @var array
     */
    private $metadataQuery = array();

    /**
     * Unix timestamp to start fetching from
     *
     * @var int
     */
    private $from;

    /**
     * Unix timestamp to fetch until
     *
     * @var int
     */
    private $to;

    /**
     * {@inheritdoc}
     */
    public function page($page = null) {
        if ($page === null) {
            return $this->page;
        }

        $this->page = (int) $page;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function limit($limit = null) {
        if ($limit === null) {
            return $this->limit;
        }

        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function returnMetadata($returnMetadata = null) {
        if ($returnMetadata === null) {
            return $this->returnMetadata;
        }

        $this->returnMetadata = (bool) $returnMetadata;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function metadataQuery(array $metadataQuery = null) {
        if ($metadataQuery === null) {
            return $this->metadataQuery;
        }

        $this->metadataQuery = $metadataQuery;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function from($from = null) {
        if ($from === null) {
            return $this->from;
        }

        $this->from = (int) $from;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function to($to = null) {
        if ($to === null) {
            return $this->to;
        }

        $this->to = (int) $to;

        return $this;
    }
}
