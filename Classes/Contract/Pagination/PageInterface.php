<?php

namespace Ttree\JsonApi\Contract\Pagination;

use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;

/**
 * Interface PageInterface
 *
 * @package Ttree\JsonApi
 */
interface PageInterface
{

    const LINK_FIRST = DocumentInterface::KEYWORD_FIRST;
    const LINK_PREV = DocumentInterface::KEYWORD_PREV;
    const LINK_NEXT = DocumentInterface::KEYWORD_NEXT;
    const LINK_LAST = DocumentInterface::KEYWORD_LAST;

    /**
     * Get the page's data.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Get a link to the first page.
     *
     * @return LinkInterface|null
     */
    public function getFirstLink();

    /**
     * Get a link to the previous page.
     *
     * @return LinkInterface|null
     */
    public function getPreviousLink();

    /**
     * Get a link to the next page.
     *
     * @return LinkInterface|null
     */
    public function getNextLink();

    /**
     * Get a link to the last page.
     *
     * @return LinkInterface|null
     */
    public function getLastLink();

    /**
     * Get pagination meta.
     *
     * @return object|array|null
     */
    public function getMeta();

    /**
     * Get the key into which page meta must be placed.
     *
     * @return string|null
     */
    public function getMetaKey();

}
