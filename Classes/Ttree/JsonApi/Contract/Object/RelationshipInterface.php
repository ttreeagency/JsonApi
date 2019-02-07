<?php

namespace Ttree\JsonApi\Contract\Object;

use Ttree\JsonApi\Exception\RuntimeException;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface as NeomerxDocumentInterface;


/**
 * Interface RelationshipInterface
 *
 * @package Ttree\JsonApi
 */
interface RelationshipInterface extends StandardObjectInterface
{

    const DATA = NeomerxDocumentInterface::KEYWORD_DATA;
    const META = NeomerxDocumentInterface::KEYWORD_META;

    /**
     * Get the data member as a correctly casted object.
     *
     * If this is a has-one relationship, a ResourceIdentifierInterface object or null will be returned. If it is
     * a has-many relationship, a ResourceIdentifierCollectionInterface will be returned.
     *
     * @return ResourceIdentifierInterface|ResourceIdentifierCollectionInterface|null
     * @throws RuntimeException
     *      if the value for the data member is not a valid relationship value.
     */
    public function getData();

    /**
     * Get the data member as a resource identifier (has-one relationship).
     *
     * @return ResourceIdentifierInterface
     * @throws RuntimeException
     *      if the data member is not a resource identifier.
     */
    public function getIdentifier();

    /**
     * Is the data member a resource identifier?
     *
     * @return bool
     */
    public function hasIdentifier();

    /**
     * Is this a has-one relationship?
     *
     * @return bool
     */
    public function isHasOne();

    /**
     * Get the data member as a has-many relationship.
     *
     * @return ResourceIdentifierCollectionInterface
     * @throws RuntimeException
     *      if the data member is not an array.
     */
    public function getIdentifiers();

    /**
     * Is this a has-many relationship?
     *
     * @return bool
     */
    public function isHasMany();

}
