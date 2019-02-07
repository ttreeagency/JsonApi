<?php

namespace Ttree\JsonApi\Contract\Object;

use Countable;
use IteratorAggregate;

/**
 * Interface ResourceIdentifierCollectionInterface
 *
 * @package Ttree\JsonApi
 */
interface ResourceIdentifierCollectionInterface extends IteratorAggregate, Countable
{

    /**
     * Does the collection contain the supplied identifier?
     *
     * @param ResourceIdentifierInterface $identifier
     * @return bool
     */
    public function has(ResourceIdentifierInterface $identifier);

    /**
     * Get the collection as an array.
     *
     * @return ResourceIdentifierInterface[]
     */
    public function getAll();

    /**
     * Is the collection empty?
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Is every identifier in the collection complete?
     *
     * @return bool
     */
    public function isComplete();

    /**
     * Does every identifier in the collection match the supplied type/any of the supplied types?
     *
     * @param string|string[] $typeOrTypes
     * @return bool
     */
    public function isOnly($typeOrTypes);

    /**
     * Get an array of the ids of each identifier in the collection.
     *
     * @return array
     */
    public function getIds();

    /**
     * Map the collection to an array of type keys and id values.
     *
     * For example, this JSON structure:
     *
     * ```
     * [
     *  {"type": "foo", "id": "1"},
     *  {"type": "foo", "id": "2"},
     *  {"type": "bar", "id": "99"}
     * ]
     * ```
     *
     * Will map to:
     *
     * ```
     * [
     *  "foo" => ["1", "2"],
     *  "bar" => ["99"]
     * ]
     * ```
     *
     * If the method call is provided with the an array `['foo' => 'FooModel', 'bar' => 'FoobarModel']`, then the
     * returned mapped array will be:
     *
     * ```
     * [
     *  "FooModel" => ["1", "2"],
     *  "FoobarModel" => ["99"]
     * ]
     * ```
     *
     * @param string[]|null $typeMap
     *      if an array, map the identifier types to the supplied types.
     * @return mixed
     */
    public function map(array $typeMap = null);
}
