<?php

namespace Ttree\JsonApi\Object;

use Ttree\JsonApi\Contract\Object\StandardObjectInterface;
use IteratorAggregate;
use OutOfBoundsException;
use stdClass;
use Traversable;

/**
 * Class StandardObject
 */
class StandardObject implements IteratorAggregate, StandardObjectInterface
{

    use ObjectProxyTrait;

    /**
     * @param object|null $proxy
     */
    public function __construct($proxy = null)
    {
        $this->proxy = $proxy ?: new stdClass();
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->proxy = Obj::replicate($this->proxy);
    }

    /**
     * @return StandardObject
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if (!$this->has($key)) {
            throw new OutOfBoundsException(sprintf('Key "%s" does not exist.', $key));
        }

        return $this->proxy->{$key};
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * @param $key
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * @return Traversable
     */
    public function getIterator()
    {
        return Obj::traverse($this->proxy);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->toArray());
    }
}
