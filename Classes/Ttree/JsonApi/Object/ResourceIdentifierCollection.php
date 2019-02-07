<?php

namespace Ttree\JsonApi\Object;

use ArrayIterator;
use Ttree\JsonApi\Contract\Object\ResourceIdentifierCollectionInterface;
use Ttree\JsonApi\Contract\Object\ResourceIdentifierInterface;
use InvalidArgumentException;

/**
 * Class ResourceIdentifierCollection
 * @package Ttree\JsonApi\Object
 */
class ResourceIdentifierCollection implements ResourceIdentifierCollectionInterface
{

    /**
     * @var array
     */
    private $stack = [];

    /**
     * @param array $identifiers
     */
    public function __construct(array $identifiers = [])
    {
        $this->addMany($identifiers);
    }

    /**
     * @param ResourceIdentifierInterface $identifier
     * @return $this
     */
    public function add(ResourceIdentifierInterface $identifier)
    {
        if (!$this->has($identifier)) {
            $this->stack[] = $identifier;
        }

        return $this;
    }

    /**
     * @param ResourceIdentifierInterface $identifier
     * @return bool
     */
    public function has(ResourceIdentifierInterface $identifier)
    {
        return \in_array($identifier, $this->stack);
    }

    /**
     * @param array $identifiers
     * @return $this
     */
    public function addMany(array $identifiers)
    {
        foreach ($identifiers as $identifier) {

            if (!$identifier instanceof ResourceIdentifierInterface) {
                throw new InvalidArgumentException('Expecting only identifier objects.');
            }

            $this->add($identifier);
        }

        return $this;
    }

    /**
     * @param array $identifiers
     * @return $this
     */
    public function setAll(array $identifiers)
    {
        $this->clear()->addMany($identifiers);

        return $this;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->stack = [];

        return $this;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getAll());
    }

    /**
     * @return int
     */
    public function count()
    {
        return \count($this->stack);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->stack);
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        /** @var ResourceIdentifier $identifier */
        foreach ($this as $identifier) {

            if (!$identifier->isComplete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $typeOrTypes
     * @return bool
     */
    public function isOnly($typeOrTypes)
    {
        /** @var ResourceIdentifier $identifier */
        foreach ($this as $identifier) {

            if (!$identifier->isType($typeOrTypes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array|null $typeMap
     * @return array
     */
    public function map(array $typeMap = null)
    {
        $ret = [];

        /** @var ResourceIdentifier $identifier */
        foreach ($this as $identifier) {

            $key = \is_array($typeMap) ? $identifier->mapType($typeMap) : $identifier->getType();

            if (!isset($ret[$key])) {
                $ret[$key] = [];
            }

            $ret[$key][] = $identifier->getId();
        }

        return $ret;
    }

    /**
     * Get the collection as an array.
     *
     * @return ResourceIdentifierInterface[]
     */
    public function getAll()
    {
        return $this->stack;
    }

    /**
     * Get an array of the ids of each identifier in the collection.
     *
     * @return array
     */
    public function getIds()
    {
        $ids = [];

        /** @var ResourceIdentifierInterface $identifier */
        foreach ($this as $identifier) {
            $ids[] = $identifier->getId();
        }

        return $ids;
    }


    /**
     * @param array $input
     * @return ResourceIdentifierCollection
     */
    public static function create(array $input)
    {
        $collection = new static();

        foreach ($input as $value) {
            $collection->add(new ResourceIdentifier($value));
        }

        return $collection;
    }
}
