<?php

namespace Flowpack\JsonApi\Object;

use Flowpack\JsonApi\Contract\Object\RelationshipInterface;
use Flowpack\JsonApi\Exception\RuntimeException;

/**
 * Class Relationship
 *
 * @package Flowpack\JsonApi
 */
class Relationship extends StandardObject implements RelationshipInterface
{

    use MetaMemberTrait;

    /**
     * @inheritdoc
     */
    public function getData()
    {
        if ($this->isHasMany()) {
            return $this->getIdentifiers();
        } elseif (!$this->isHasOne()) {
            throw new RuntimeException('No data member or data member is not a valid relationship.');
        }

        return $this->hasIdentifier() ? $this->getIdentifier() : null;
    }


    /**
     * @inheritdoc
     */
    public function getIdentifier()
    {
        if (!$this->isHasOne()) {
            throw new RuntimeException('No data member or data member is not a valid has-one relationship.');
        }

        $data = $this->{self::DATA};

        if (!$data) {
            throw new RuntimeException('No resource identifier - relationship is empty.');
        }

        return new ResourceIdentifier($data);
    }

    /**
     * @inheritdoc
     */
    public function hasIdentifier()
    {
        return is_object($this->{self::DATA});
    }

    /**
     * @inheritdoc
     */
    public function isHasOne()
    {
        if (!$this->has(self::DATA)) {
            return false;
        }

        $data = $this->{self::DATA};

        return \is_null($data) || \is_object($data);
    }

    /**
     * @inheritdoc
     */
    public function getIdentifiers()
    {
        if (!$this->isHasMany()) {
            throw new RuntimeException('No data member of data member is not a valid has-many relationship.');
        }

        return ResourceIdentifierCollection::create($this->{self::DATA});
    }

    /**
     * @inheritdoc
     */
    public function isHasMany()
    {
        return \is_array($this->{self::DATA});
    }
}
