<?php

namespace Ttree\JsonApi\Contract\Object;

use Ttree\JsonApi\Exception\RuntimeException;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface as NeomerxDocumentInterface;

/**
 * Interface ResourceObjectInterface
 *
 * @package Ttree\JsonApi
 */
interface ResourceObjectInterface
{

    const TYPE = NeomerxDocumentInterface::KEYWORD_TYPE;
    const ID = NeomerxDocumentInterface::KEYWORD_ID;
    const ATTRIBUTES = NeomerxDocumentInterface::KEYWORD_ATTRIBUTES;
    const RELATIONSHIPS = NeomerxDocumentInterface::KEYWORD_RELATIONSHIPS;
    const META = NeomerxDocumentInterface::KEYWORD_META;

    /**
     * Get the type member.
     *
     * @return string
     * @throws RuntimeException
     *      if no type is set, is empty or is not a string.
     */
    public function getType();

    /**
     * @return string|int
     * @throws RuntimeException
     *      if no id is set, is not a string or integer, or is an empty string.
     */
    public function getId();

    /**
     * @return bool
     */
    public function hasId();

    /**
     * Get the type and id members as a resource identifier object.
     *
     * @return ResourceIdentifierInterface
     * @throws RuntimeException
     *      if the type and/or id members are not valid.
     */
    public function getIdentifier();

    /**
     * @return StandardObjectInterface
     * @throws RuntimeException
     *      if the attributes member is present and is not an object.
     */
    public function getAttributes();

    /**
     * @return bool
     */
    public function hasAttributes();

    /**
     * @return RelationshipsInterface
     * @throws RuntimeException
     *      if the relationships member is present and is not an object.
     */
    public function getRelationships();

    /**
     * @return bool
     */
    public function hasRelationships();

    /**
     * Get a relationship object by its key.
     *
     * @param string $key
     * @return RelationshipInterface|null
     *      the relationship object or null if it is not present.
     * @throws RuntimeException
     */
    public function getRelationship($key);

}
