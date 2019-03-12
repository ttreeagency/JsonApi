<?php

namespace Ttree\JsonApi\Adapter;

use Neos\Flow\Annotations\Entity;
use Ttree\JsonApi\Contract\Adapter\RelationshipAdapterInterface;
use Ttree\JsonApi\Contract\Adapter\ResourceAdapterInterface;
use Ttree\JsonApi\Contract\Object\ResourceObjectInterface;
use Ttree\JsonApi\Contract\Object\StandardObjectInterface;
use Ttree\JsonApi\Contract\Object\RelationshipInterface;
use Ttree\JsonApi\Contract\Object\RelationshipsInterface;
use Ttree\JsonApi\Exception\RuntimeException;
use Ttree\JsonApi\Mvc\Controller\EncodingParametersParser;
use Ttree\JsonApi\Utility\StringUtility as Str;

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
     * @param ResourceObjectInterface $resource
     * @param EncodingParametersParser $parameters
     * @return object
     * @throws RuntimeException
     */
    public function create(ResourceObjectInterface $resource, EncodingParametersParser $parameters)
    {
        $record = $this->createRecord($resource);
        $this->hydrateAttributes($record, $resource->getAttributes());
        $this->fillRelationships($record, $resource->getRelationships(), $parameters);
        $record = $this->persist($record) ?: $record;

        if (\method_exists($this, 'hydrateRelated')) {
            $record = $this->hydrateRelated($record, $resource, $parameters) ?: $record;
        }

        return $record;
    }

    /**
     * @param string $resourceId
     * @param EncodingParametersParser $parameters
     * @return null|object
     */
    public function read($resourceId, EncodingParametersParser $parameters)
    {
        return $this->find($resourceId);
    }

    /**
     * @param object $record
     * @param ResourceObjectInterface $resource
     * @param EncodingParametersParser $parameters
     * @return object
     * @throws RuntimeException
     */
    public function update($record, ResourceObjectInterface $resource, EncodingParametersParser $parameters)
    {
        $this->hydrateAttributes($record, $resource->getAttributes());
        $this->fillRelationships($record, $resource->getRelationships(), $parameters);
        $record = $this->persist($record) ?: $record;

        if (\method_exists($this, 'hydrateRelated')) {
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
     * @param $field
     * @return string|null
     */
    protected function methodForRelation($field)
    {
        $method = Str::camelize($field);
        return \method_exists($this, $method) ? $method : null;
    }

    /**
     * @param $record
     * @param RelationshipsInterface $relationships
     * @param EncodingParametersParser $parameters
     * @throws RuntimeException
     */
    protected function fillRelationships(
        $record,
        RelationshipsInterface $relationships,
        EncodingParametersParser $parameters
    )
    {
        foreach ($relationships->getAll() as $field => $relationship) {
            /** @todo Skip any fields that are not fillable. */

            /** Skip any fields that are not relations */
            if (!$this->isRelation($field)) {
                continue;
            }

            $this->fillRelationship(
                $record,
                $field,
                $relationships->getRelationship($field),
                $parameters
            );
        }
    }

    /**
     * @param $record
     * @param $field
     * @param RelationshipInterface $relationship
     * @param EncodingParametersParser $parameters
     * @throws RuntimeException
     */
    protected function fillRelationship(
        $record,
        $field,
        RelationshipInterface $relationship,
        EncodingParametersParser $parameters
    )
    {
        $relation = $this->related($field);
        $relation->update($record, $relationship, $parameters);
    }

}
