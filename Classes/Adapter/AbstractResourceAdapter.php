<?php

namespace Flowpack\JsonApi\Adapter;

use Neos\Flow\Annotations\Entity;
use Flowpack\JsonApi\Contract\Adapter\RelationshipAdapterInterface;
use Flowpack\JsonApi\Contract\Adapter\ResourceAdapterInterface;
use Flowpack\JsonApi\Contract\Object\ResourceObjectInterface;
use Flowpack\JsonApi\Contract\Object\StandardObjectInterface;
use Flowpack\JsonApi\Contract\Object\RelationshipInterface;
use Flowpack\JsonApi\Contract\Object\RelationshipsInterface;
use Flowpack\JsonApi\Exception\RuntimeException;
use Flowpack\JsonApi\Mvc\Controller\EncodingParametersParser;
use Flowpack\JsonApi\Utility\StringUtility as Str;

/**
 * Class AbstractResourceAdaptor
 */
abstract class AbstractResourceAdapter implements ResourceAdapterInterface
{

    /**
     * @param $record
     * @param StandardObjectInterface $attributes
     * @param null $id
     * @return void
     */
    abstract protected function hydrateAttributes($record, StandardObjectInterface $attributes, $id = null);

    /**
     * Persist changes to the record.
     *
     * @param $record
     * @return object
     */
    abstract protected function persist($record);

    /**
     * @param $propertyMappedResource
     * @param ResourceObjectInterface $resourceObject
     * @param EncodingParametersParser $parameters
     * @return object
     */
    public function create($propertyMappedResource, ResourceObjectInterface $resourceObject, EncodingParametersParser $parameters)
    {
        $this->beforeCreate($propertyMappedResource, $resourceObject, $parameters);

        $persistedResource = $this->persist($propertyMappedResource);

        $this->beforeCreate($persistedResource, $resourceObject, $parameters);

        return $persistedResource;
    }

    /**
     * @param $propertyMappedResource
     * @param ResourceObjectInterface $resourceObject
     * @param EncodingParametersParser $parameters
     */
    protected function beforeCreate($propertyMappedResource, ResourceObjectInterface $resourceObject, EncodingParametersParser $parameters)
    {
    }

    /**
     * @param $persistedResource
     * @param ResourceObjectInterface $resourceObject
     * @param EncodingParametersParser $parameters
     */
    protected function afterCreate($persistedResource, ResourceObjectInterface $resourceObject, EncodingParametersParser $parameters)
    {
    }

    /**
     * @param $propertyMappedResource
     * @param ResourceObjectInterface $resourceObject
     * @param EncodingParametersParser $parameters
     * @return object
     */
    public function update($propertyMappedResource, ResourceObjectInterface $resourceObject, EncodingParametersParser $parameters)
    {
        $this->beforeUpdate($propertyMappedResource, $resourceObject, $parameters);

        $persistedResource = $this->persist($propertyMappedResource);

        $this->beforeUpdate($persistedResource, $resourceObject, $parameters);

        return $persistedResource;
    }

    /**
     * @param $propertyMappedResource
     * @param ResourceObjectInterface $resourceObject
     * @param EncodingParametersParser $parameters
     */
    protected function beforeUpdate($propertyMappedResource, ResourceObjectInterface $resourceObject, EncodingParametersParser $parameters)
    {
    }

    /**
     * @param $persistedResource
     * @param ResourceObjectInterface $resourceObject
     * @param EncodingParametersParser $parameters
     */
    protected function afterUpdate($persistedResource, ResourceObjectInterface $resourceObject, EncodingParametersParser $parameters)
    {
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
     * @param $propertyMappedResource
     * @param EncodingParametersParser $parameters
     */
    protected function beforeDelete($propertyMappedResource, EncodingParametersParser $parameters)
    {
    }

    /**
     * @param $persistedResource
     * @param EncodingParametersParser $parameters
     */
    protected function afterDelete($persistedResource, EncodingParametersParser $parameters)
    {
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
