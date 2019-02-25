<?php

namespace Ttree\JsonApi\Contract\Adapter;

use Ttree\JsonApi\Contract\Object\RelationshipInterface;
use Ttree\JsonApi\Contract\Parameters\EncodingParametersInterface;

interface HasManyAdapterInterface extends RelationshipAdapterInterface
{

    /**
     * Add data to a domain record's relationship using data from the supplied relationship object.
     *
     * For a has-many relationship, this adds the resource identifiers in the relationship to the domain
     * record's relationship. It is not valid for a has-one relationship.
     *
     * @param object $record
     *      the object to hydrate.
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     * @return object
     *      the updated domain record.
     */
    public function add($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters);

    /**
     * Remove data from a domain record's relationship using data from the supplied relationship object.
     *
     * For a has-many relationship, this removes the resource identifiers in the relationship from the
     * domain record's relationship. It is not valid for a has-one relationship, as `update()` must
     * be used instead.
     *
     * @param object $record
     *      the object to hydrate.
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     * @return object
     *      the updated domain record.
     */
    public function remove($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters);

}
