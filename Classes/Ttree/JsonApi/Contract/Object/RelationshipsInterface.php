<?php

namespace Ttree\JsonApi\Contract\Object;

use Ttree\JsonApi\Exception\RuntimeException;
use Traversable;

/**
 * Interface RelationshipsInterface
 *
 * @package Ttree\JsonApi
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
