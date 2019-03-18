<?php

namespace Flowpack\JsonApi\Object;

/**
 * Trait ObjectProxyTrait
 * @package Flowpack\JsonApi\Object
 */
trait ObjectProxyTrait
{

    /**
     * @var object
     */
    protected $proxy;

    /**
     * @param string $key
     * @param mixed $default
     * @return StandardObject|mixed
     */
    public function get($key, $default = null)
    {
        return Obj::get($this->proxy, $key, $default);
    }

    /**
     * @param array ...$keys
     * @return array
     */
    public function getProperties(...$keys)
    {
        $values = [];

        foreach ($this->normalizeKeys($keys) as $key) {
            $values[$key] = $this->has($key) ? $this->proxy->{$key} : null;
        }

        return $values;
    }

    /**
     * Get properties if they exist.
     *
     * @param array ...$keys
     * @return array
     */
    public function getMany(...$keys)
    {
        $ret = [];

        foreach ($this->normalizeKeys($keys) as $key) {
            if ($this->has($key)) {
                $ret[$key] = $this->proxy->{$key};
            }
        }

        return $ret;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->proxy->{$key} = $value;

        return $this;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function setProperties(array $values)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Set the property if it does not already exist.
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function add($key, $value)
    {
        if (!$this->has($key)) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Add many properties.
     *
     * @param array $values
     * @return $this
     */
    public function addProperties(array $values)
    {
        foreach ($values as $key => $value) {
            $this->add($key, $value);
        }

        return $this;
    }

    /**
     * @param array ...$keys
     * @return bool
     */
    public function has(...$keys)
    {
        foreach ($this->normalizeKeys($keys) as $key) {
            if (!\property_exists($this->proxy, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array ...$keys
     * @return bool
     */
    public function hasAny(...$keys)
    {
        foreach ($this->normalizeKeys($keys) as $key) {
            if ($this->has($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array ...$keys
     * @return $this
     */
    public function remove(...$keys)
    {
        foreach ($this->normalizeKeys($keys) as $key) {
            unset($this->proxy->{$key});
        }

        return $this;
    }

    /**
     * Reduce this object so that it only has the supplied allowed keys.
     *
     * @param array ...$keys
     * @return $this
     */
    public function reduce(...$keys)
    {
        $keys = $this->normalizeKeys($keys);

        foreach ($this->keys() as $key) {
            if (!\in_array($key, $keys, true)) {
                $this->remove($key);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function keys()
    {
        return \array_keys(\get_object_vars($this->proxy));
    }

    /**
     * If the object has the current key, rename it to the new key name.
     *
     * @param $currentKey
     * @param $newKey
     * @return $this
     */
    public function rename($currentKey, $newKey)
    {
        if ($this->has($currentKey)) {
            $this->set($newKey, $this->proxy->{$currentKey})->remove($currentKey);
        }

        return $this;
    }

    /**
     * Rename many current keys to new keys.
     *
     * @param array $mapping
     * @return $this
     */
    public function renameKeys(array $mapping)
    {
        foreach ($mapping as $currentKey => $newKey) {
            $this->rename($currentKey, $newKey);
        }

        return $this;
    }

    /**
     * @param callable $transform
     * @param array ...$keys
     * @return $this
     */
    public function transform(callable $transform, ...$keys)
    {
        foreach ($this->normalizeKeys($keys) as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $value = \call_user_func($transform, $this->proxy->{$key});
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * @param callable $transform
     * @return $this
     */
    public function transformKeys(callable $transform)
    {
        Obj::transformKeys($this->proxy, $transform);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return Obj::toArray($this->proxy);
    }

    /**
     * @return object
     */
    public function toStdClass()
    {
        return Obj::replicate($this->proxy);
    }

    /**
     * @return object
     */
    public function jsonSerialize()
    {
        return $this->proxy;
    }

    /**
     * @param array $keys
     * @return array
     */
    protected function normalizeKeys(array $keys)
    {
        return ($keys && \is_array($keys[0])) ? (array)$keys[0] : $keys;
    }
}
