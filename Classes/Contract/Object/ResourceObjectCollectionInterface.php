<?php

namespace Flowpack\JsonApi\Contract\Object;

use Flowpack\JsonApi\Exception\RuntimeException;
use Countable;
use IteratorAggregate;

/**
 * Interface ResourceIdentifierCollectionInterface
 *
 * @package Flowpack\JsonApi
 */
interface ResourceObjectCollectionInterface extends IteratorAggregate, Countable
{

    /**
     * Does the collection contain a resource with the supplied identifier?
     *
     * @param ResourceIdentifierInterface $identifier
     * @return bool
     */
    public function has(ResourceIdentifierInterface $identifier);

    /**
     * Get the resource with the supplied identifier.
     *
     * @param ResourceIdentifierInterface $identifier
     * @return ResourceObjectInterface
     * @throws RuntimeException
     *      if the collection does not contain a resource that matches the supplied identifier.
     */
    public function get(ResourceIdentifierInterface $identifier);

    /**
     * Get the collection as an array.
     *
     * @return ResourceObjectInterface[]
     */
    public function getAll();

    /**
     * Get all the resource identifiers of the resources in the collection
     *
     * @return ResourceIdentifierCollectionInterface
     */
    public function getIdentifiers();

    /**
     * Is the collection empty?
     *
     * @return bool
     */
    public function isEmpty();

}
