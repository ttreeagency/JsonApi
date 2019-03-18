<?php

namespace Flowpack\JsonApi\JsonApi\PersistentResource;

use Neos\Flow\Annotations as Flow;
use Flowpack\JsonApi\Adapter\AbstractAdapter;

/**
 * Class Adapter
 * @package Flowpack\JsonApi\JsonApi\PersistentResource
 * @Flow\Scope("singleton")
 */
class Adapter extends AbstractAdapter
{
    /**
     * Map attributes to different named properties
     * @var array
     */
    protected $mapAttributeToProperty = [];

    /**
     * @param \Neos\Flow\Persistence\QueryInterface $query
     * @param array|null $filters
     */
    public function filter($query, $filters)
    {
    }
}