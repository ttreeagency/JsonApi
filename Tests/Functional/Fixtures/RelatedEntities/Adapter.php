<?php

namespace Ttree\JsonApi\Tests\Functional\Fixtures\RelatedEntities;

use Neos\Flow\Annotations as Flow;
use Ttree\JsonApi\Adapter\AbstractAdapter;

/**
 *
 * Class DefaultAdapter
 * @package Ttree\JsonApi\Adapter
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
     * @return \Ttree\JsonApi\Domain\BelongsTo
     */
    protected function createdBy()
    {
        return $this->belongsTo();
    }

    /**
     * @return \Ttree\JsonApi\Domain\HasMany
     */
    protected function comments()
    {
        return $this->hasMany();
    }
}
