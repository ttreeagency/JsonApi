<?php

namespace Ttree\JsonApi\Object;

use ArrayIterator;
use Ttree\JsonApi\Contract\Object\ResourceIdentifierInterface;
use Ttree\JsonApi\Contract\Object\ResourceObjectInterface;
use Ttree\JsonApi\Contract\Object\ResourceObjectCollectionInterface;

use Ttree\JsonApi\Exception\RuntimeException;
use InvalidArgumentException;

/**
 * Class ResourceCollection
 *
 * @package Ttree\JsonApi
 */
class ResourceObjectCollection implements ResourceObjectCollectionInterface
{

    /**
     * @var ResourceObjectInterface[]
     */
    private $stack = [];

    /**
     * @param array $resources
     * @return ResourceObjectCollection
     */
    public static function create(array $resources)
    {
        $resources = \array_map(function ($resource) {
            return ($resource instanceof ResourceObjectInterface) ? $resource : new ResourceObject($resource);
        }, $resources);

        return new self($resources);
    }

    /**
     * ResourceCollection constructor.
     *
     * @param array $resources
     */
    public function __construct(array $resources = [])
    {
        $this->addMany($resources);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->stack);
    }

    /**
     * @inheritDoc
     */
    public function has(ResourceIdentifierInterface $identifier)
    {
        /** @var ResourceObjectInterface $resource */
        foreach ($this as $resource) {

            if ($identifier->isSame($resource->getIdentifier())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function get(ResourceIdentifierInterface $identifier)
    {
        /** @var ResourceObjectInterface $resource */
        foreach ($this as $resource) {

            if ($identifier->isSame($resource->getIdentifier())) {
                return $resource;
            }
        }

        throw new RuntimeException('No matching resource in collection: ' . $identifier->toString());
    }

    /**
     * @inheritDoc
     */
    public function getAll()
    {
        return $this->stack;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifiers()
    {
        $collection = new ResourceIdentifierCollection();

        /** @var ResourceObjectInterface $resource */
        foreach ($this as $resource) {
            $collection->add($resource->getIdentifier());
        }

        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty()
    {
        return empty($this->stack);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return \count($this->stack);
    }

    /**
     * @param ResourceObjectInterface $resource
     * @return $this
     */
    public function add(ResourceObjectInterface $resource)
    {
        if (!$this->has($resource->getIdentifier())) {
            $this->stack[] = $resource;
        }

        return $this;
    }

    /**
     * @param array $resources
     * @return $this
     */
    public function addMany(array $resources)
    {
        foreach ($resources as $resource) {

            if (!$resource instanceof ResourceObjectInterface) {
                throw new InvalidArgumentException('Expecting only resource objects.');
            }

            $this->add($resource);
        }

        return $this;
    }
}
