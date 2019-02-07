<?php

namespace Ttree\JsonApi\Contract\Object;

use Neomerx\JsonApi\Contracts\Schema\DocumentInterface as NeomerxDocumentInterface;
use Neomerx\JsonApi\Schema\ErrorCollection;
use Ttree\JsonApi\Exception\RuntimeException;

/**
 * Interface DocumentInterface
 *
 * @package CloudCreativity\LaravelJsonApi
 */
interface DocumentInterface extends StandardObjectInterface, MetaMemberInterface
{

    const DATA = NeomerxDocumentInterface::KEYWORD_DATA;
    const META = NeomerxDocumentInterface::KEYWORD_META;
    const INCLUDED = NeomerxDocumentInterface::KEYWORD_INCLUDED;
    const ERRORS = NeomerxDocumentInterface::KEYWORD_ERRORS;

    /**
     * Get the data member of the document as a standard object or array
     *
     * @return StandardObjectInterface|array|null
     * @throws RuntimeException
     *      if the data member is not present, or is not an object, array or null.
     */
    public function getData();

    /**
     * Get the data member as a resource object.
     *
     * @return ResourceObjectInterface
     * @throws RuntimeException
     *      if the data member is not an object or is not present.
     */
    public function getResource();

    /**
     * Get the data member as a resource object collection.
     *
     * @return ResourceObjectCollectionInterface
     * @throws RuntimeException
     *      if the data member is not an array or is not present.
     */
    public function getResources();

    /**
     * Get the document as a relationship.
     *
     * @return RelationshipInterface
     */
    public function getRelationship();

    /**
     * Get the included member as a resource object collection.
     *
     * @return ResourceObjectCollectionInterface|null
     *      the resources or null if the included member is not present.
     */
    public function getIncluded();

    /**
     * Get the errors member as an error collection.
     *
     * @return ErrorCollection|null
     *      the errors or null if the error member is not present.
     */
    public function getErrors();

}
