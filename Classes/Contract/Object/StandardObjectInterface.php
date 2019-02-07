<?php

namespace Ttree\JsonApi\Contract\Object;

use Countable;
use JsonSerializable;
use stdClass;
use Traversable;

/**
 * Interface StandardObjectInterface
 *
 * @package Ttree\JsonApi
 */
interface StandardObjectInterface extends Traversable, Countable, JsonSerializable
{

    /**
     * @param $key
     * @param $default
     * @return StandardObjectInterface|mixed
     */
    public function get($key, $default = null);

    /**
     * Get properties.
     *
     * @param string|string[] ...$keys
     * @return mixed
     */
    public function getProperties(...$keys);

    /**
     * Get properties if they exist.
     *
     * @param string|string[] ...$keys
     * @return array
     */
    public function getMany(...$keys);

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value);

    /**
     * @param array $values
     * @return $this
     */
    public function setProperties(array $values);

    /**
     * Set the key's value, if the key does not exist on the object.
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function add($key, $value);

    /**
     * Add many properties.
     *
     * @param array $values
     * @return $this
     */
    public function addProperties(array $values);

    /**
     * Do all the key(s) exist?
     *
     * @param array ...$keys
     * @return bool
     */
    public function has(...$keys);

    /**
     * Whether the object has any (at least one) of the specified keys.
     *
     * @param array ...$keys
     * @return bool
     */
    public function hasAny(...$keys);

    /**
     * @param array ...$key
     * @return $this
     */
    public function remove(...$key);

    /**
     * Reduce this object so that it only has the supplied allowed keys.
     *
     * @param array ...$keys
     * @return $this
     */
    public function reduce(...$keys);

    /**
     * Fluent clone method.
     *
     * @return StandardObjectInterface
     */
    public function copy();

    /**
     * Get a list of the object's keys.
     *
     * @return string[]
     */
    public function keys();

    /**
     * If the object has the current key, rename it to the new key.
     *
     * @param $currentKey
     * @param $newKey
     * @return $this
     */
    public function rename($currentKey, $newKey);

    /**
     * Rename multiple keys to new key names.
     *
     * @param array $mapping
     * @return $this
     */
    public function renameKeys(array $mapping);

    /**
     * Apply the transform to the value for the supplied key(s), if it exists.
     *
     * @param callable $transform
     * @param array $keys
     * @return $this
     */
    public function transform(callable $transform, ...$keys);

    /**
     * Recursively iterate through the object's keys and apply the transform to each key.
     *
     * @param callable $transform
     * @return $this
     */
    public function transformKeys(callable $transform);

    /**
     * Get the object's property values as an array.
     *
     * @return array
     */
    public function toArray();

    /**
     * @return stdClass
     */
    public function toStdClass();

}
