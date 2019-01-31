<?php

namespace Ttree\JsonApi\Adapter;

use Neos\Flow\Annotations as Flow;

/**
 *
 * Class DefaultAdapter
 * @package Ttree\JsonApi\Adapter
 * @api
 */
class DefaultAdapter extends AbstractAdapter
{
    /**
     * Map attributes to different named properties
     * @var array
     */
    protected $attributesMapping = [];

    /**
     * @param \Neos\Flow\Persistence\QueryInterface $query
     * @param array|null $filters
     */
    public function filter($query, $filters)
    {
    }
}