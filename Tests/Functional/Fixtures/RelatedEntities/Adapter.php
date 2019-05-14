<?php

namespace Flowpack\JsonApi\Tests\Functional\Fixtures\RelatedEntities;

use Neos\Flow\Annotations as Flow;
use Flowpack\JsonApi\Adapter\AbstractAdapter;

/**
 *
 * Class DefaultAdapter
 * @package Flowpack\JsonApi\Adapter
 * @api
 */
class Adapter extends AbstractAdapter
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

    /**
     * @return \Flowpack\JsonApi\Domain\BelongsTo
     */
    protected function createdBy()
    {
        return $this->belongsTo();
    }

    /**
     * @return \Flowpack\JsonApi\Domain\HasMany
     */
    protected function comments()
    {
        return $this->hasMany();
    }
}