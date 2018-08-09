<?php

namespace Ttree\JsonApi\Object;

use Ttree\JsonApi\Contract\Object\ResourceIdentifierInterface;
use Ttree\JsonApi\Exception\RuntimeException;

/**
 * Class ResourceIdentifier
 *
 * @package Ttree\JsonApi
 */
class ResourceIdentifier extends StandardObject implements ResourceIdentifierInterface
{

    use IdentifiableTrait,
        MetaMemberTrait;

    /**
     * @param $type
     * @param $id
     * @return ResourceIdentifier
     */
    public static function create($type, $id)
    {
        $identifier = new self();

        $identifier->set(self::TYPE, $type)
            ->set(self::ID, $id);

        return $identifier;
    }

    /**
     * @inheritDoc
     */
    public function isType($typeOrTypes)
    {
        return in_array($this->get(self::TYPE), (array) $typeOrTypes, true);
    }

    /**
     * @inheritDoc
     */
    public function mapType(array $map)
    {
        $type = $this->getType();

        if (array_key_exists($type, $map)) {
            return $map[$type];
        }

        throw new RuntimeException(sprintf('Type "%s" is not in the supplied map.', $type));
    }

    /**
     * @inheritDoc
     */
    public function isComplete()
    {
        return $this->hasType() && $this->hasId();
    }

    /**
     * @inheritDoc
     */
    public function isSame(ResourceIdentifierInterface $identifier)
    {
        return $this->getType() === $identifier->getType() &&
            $this->getId() === $identifier->getId();
    }

    /**
     * @inheritDoc
     */
    public function toString()
    {
        return sprintf('%s:%s', $this->getType(), $this->getId());
    }

}
