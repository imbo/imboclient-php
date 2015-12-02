<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient;

/**
 * Query object for various commands
 *
 * @package Client
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class Query {
    /**
     * The page to get
     *
     * @var int
     */
    protected $page = 1;

    /**
     * Number of items to get
     *
     * @var int
     */
    protected $limit = 20;

    /**
     * Set or get the page property
     *
     * @param int $page Give this a value to set the page property
     * @return int|self
     */
    public function page($page = null) {
        if ($page === null) {
            return $this->page;
        }

        $this->page = (int) $page;

        return $this;
    }

    /**
     * Set or get the limit property
     *
     * @param int $limit Give this a value to set the limit property
     * @return int|self
     */
    public function limit($limit = null) {
        if ($limit === null) {
            return $this->limit;
        }

        $this->limit = (int) $limit;

        return $this;
    }
}
