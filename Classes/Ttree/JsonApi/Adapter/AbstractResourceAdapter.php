<?php

namespace Ttree\JsonApi\Adapter;

use Ttree\JsonApi\Contract\Adapter\ResourceAdapterInterface;
use Ttree\JsonApi\Contract\Object\ResourceObjectInterface;
use Ttree\JsonApi\Contract\Object\StandardObjectInterface;
use Ttree\JsonApi\Contract\Object\RelationshipInterface;
use Ttree\JsonApi\Contract\Object\RelationshipsInterface;
use Ttree\JsonApi\Exception\RuntimeException;
use Ttree\JsonApi\Utility\StringUtility as Str;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;

/**
 * Class AbstractResourceAdaptor
 */
abstract class AbstractResourceAdapter implements ResourceAdapterInterface
{

    /**
     * Create a new record.
     *
     * Implementing classes need only implement the logic to transfer the minimum
     * amount of data from the resource that is required to construct a new record
     * instance. The adapter will then hydrate the object after it has been
     * created.
     *
     * @param ResourceObjectInterface $resource
     * @return object
     */
    abstract protected function createRecord(ResourceObjectInterface $resource);

    /**
     * @param $record
     * @param StandardObjectInterface $attributes
     * @return void
     */
    abstract protected function hydrateAttributes($record, StandardObjectInterface $attributes);

    /**
     * Persist changes to the record.
     *
     * @param $record
     * @return object|void
     */
    abstract protected function persist($record);

    /**
     * @todo
     * @inheritdoc
     */
    public function create(ResourceObjectInterface $resource, EncodingParametersInterface $parameters)
    {
        $record = $this->createRecord($resource);
        $this->hydrateAttributes($record, $resource->getAttributes());
//        $this->hydrateRelationships($record, $resource->getRelationships(), $parameters);
        $record = $this->persist($record) ?: $record;

        if (method_exists($this, 'hydrateRelated')) {
            $record = $this->hydrateRelated($record, $resource, $parameters) ?: $record;
        }

        return $record;
    }

    /**
     * @inheritDoc
     */
    public function read($resourceId, EncodingParametersInterface $parameters)
    {
        return $this->find($resourceId);
    }

    /**
     * @todo
     * @inheritdoc
     */
    public function update($record, ResourceObjectInterface $resource, EncodingParametersInterface $parameters)
    {
        $this->hydrateAttributes($record, $resource->getAttributes());
//        $this->hydrateRelationships($record, $resource->getRelationships(), $parameters);
        $record = $this->persist($record) ?: $record;

        if (method_exists($this, 'hydrateRelated')) {
            $record = $this->hydrateRelated($record, $resource, $parameters) ?: $record;
        }

        return $record;
    }

    /**
     * @todo
     * @inheritDoc
     */
    public function related($field)
    {
        if (!$method = $this->methodForRelation($field)) {
            throw new RuntimeException("No relationship method implemented for field {$field}.");
        }

        $relation = $this->{$method}();

        if (!$relation instanceof RelationshipAdapterInterface) {
            throw new RuntimeException("Method {$method} did not return a relationship adapter.");
        }

        $relation->withFieldName($field);

//        if ($relation instanceof StoreAwareInterface) {
//            $relation->withStore($this->store());
//        }

        return $relation;
    }

    /**
     * @param $field
     * @return bool
     */
    protected function isRelation($field)
    {
        return !empty($this->methodForRelation($field));
    }

    /**
     * @todo
     * @param $field
     * @return string|null
     */
    protected function methodForRelation($field)
    {
//        $method = Str::camelize($field);

        return method_exists($this, $method) ? $method : null;
    }

    /**
     * @todo
     * @param $record
     * @param RelationshipsInterface $relationships
     * @param EncodingParametersInterface $parameters
     * @return void
     */
    protected function hydrateRelationships(
        $record,
        RelationshipsInterface $relationships,
        EncodingParametersInterface $parameters
    )
    {
        foreach ($relationships->getAll() as $field => $relationship) {
//            /** Skip any fields that are not fillable. */
//            if ($this->isNotFillable($field, $record)) {
//                continue;
//            }

//            /** Skip any fields that are not relations */
//            if (!$this->isRelation($field)) {
//                continue;
//            }

            $this->hydrateRelationship(
                $record,
                $field,
                $relationships->getRelationship($field),
                $parameters
            );
        }
    }

    /**
     * @todo
     * Fill a relationship from a resource object.
     *
     * @param $record
     * @param $field
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     */
    protected function hydrateRelationship(
        $record,
        $field,
        RelationshipInterface $relationship,
        EncodingParametersInterface $parameters
    )
    {
        $relation = $this->related($field);

//        $relation->update($record, $relationship, $parameters);
    }

}
