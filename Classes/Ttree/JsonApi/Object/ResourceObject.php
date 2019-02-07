<?php

namespace Ttree\JsonApi\Object;

use Ttree\JsonApi\Contract\Object\ResourceObjectInterface;
use Ttree\JsonApi\Contract\Object\StandardObjectInterface;
use Ttree\JsonApi\Exception\RuntimeException;

/**
 * Class Resource
 */
class ResourceObject extends StandardObject implements ResourceObjectInterface
{

    use IdentifiableTrait,
        MetaMemberTrait;

    /**
     * @inheritdoc
     */
    public function getIdentifier()
    {
        return ResourceIdentifier::create($this->getType(), $this->getId());
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        $attributes = $this->hasAttributes() ? $this->get(self::ATTRIBUTES) : new StandardObject();

        if (!$attributes instanceof StandardObjectInterface) {
            throw new RuntimeException('Attributes member is not an object.');
        }

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function hasAttributes()
    {
        return $this->has(self::ATTRIBUTES);
    }

    /**
     * @inheritdoc
     */
    public function getRelationships()
    {
        $relationships = $this->hasRelationships() ? $this->{self::RELATIONSHIPS} : null;

        if (!is_null($relationships) && !is_object($relationships)) {
            throw new RuntimeException('Relationships member is not an object.');
        }

        return new Relationships($relationships);
    }

    /**
     * @inheritdoc
     */
    public function hasRelationships()
    {
        return $this->has(self::RELATIONSHIPS);
    }

    /**
     * @inheritDoc
     */
    public function getRelationship($key)
    {
        $relationships = $this->getRelationships();

        return $relationships->has($key) ? $relationships->getRelationship($key) : null;
    }

}
