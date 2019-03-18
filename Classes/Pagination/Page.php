<?php

namespace Flowpack\JsonApi\Pagination;

use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Flowpack\JsonApi\Contract\Pagination\PageInterface;

/**
 * Class Page
 *
 * @package Flowpack\JsonApi
 */
class Page implements PageInterface
{

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var LinkInterface|null
     */
    private $first;

    /**
     * @var LinkInterface|null
     */
    private $previous;

    /**
     * @var LinkInterface|null
     */
    private $next;

    /**
     * @var LinkInterface|null
     */
    private $last;

    /**
     * @var array|null|object
     */
    private $meta;

    /**
     * @var string|null
     */
    private $metaKey;

    /**
     * Page constructor.
     *
     * @param $data
     * @param LinkInterface|null $first
     * @param LinkInterface|null $previous
     * @param LinkInterface|null $next
     * @param LinkInterface|null $last
     * @param object|array|null $meta
     * @param string|null $metaKey
     */
    public function __construct(
        $data,
        LinkInterface $first = null,
        LinkInterface $previous = null,
        LinkInterface $next = null,
        LinkInterface $last = null,
        $meta = null,
        $metaKey = null
    )
    {
        $this->data = $data;
        $this->first = $first;
        $this->previous = $previous;
        $this->next = $next;
        $this->last = $last;
        $this->meta = $meta;
        $this->metaKey = $metaKey;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function getFirstLink()
    {
        return $this->first;
    }

    /**
     * @inheritDoc
     */
    public function getPreviousLink()
    {
        return $this->previous;
    }

    /**
     * @inheritDoc
     */
    public function getNextLink()
    {
        return $this->next;
    }

    /**
     * @inheritDoc
     */
    public function getLastLink()
    {
        return $this->last;
    }

    /**
     * @inheritDoc
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @inheritDoc
     */
    public function getMetaKey()
    {
        return $this->metaKey;
    }
}
