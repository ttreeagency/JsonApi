<?php

namespace Flowpack\JsonApi\Contract\Object;

use Flowpack\JsonApi\Exception\RuntimeException;
use Traversable;

/**
 * Interface RelationshipsInterface
 *
 * @package Flowpack\JsonApi
 */
interface RelationshipsInterface extends StandardObjectInterface
{

    /**
     * Get a traversable object of keys to relationship objects.
     *
     * This iterator will return all keys with values cast to `RelationshipInterface` objects.
     *
     * @return Traversable
     */
    public function getAll();

    /**
     * @param $key
     * @return RelationshipInterface
     * @throws RuntimeException
     *      if the key is not present, or is not an object.
     */
    public function getRelationship($key);

}
