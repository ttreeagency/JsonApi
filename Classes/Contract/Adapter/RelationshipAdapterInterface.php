<?php

namespace Flowpack\JsonApi\Contract\Adapter;

use Flowpack\JsonApi\Contract\Object\RelationshipInterface;
use Flowpack\JsonApi\Contract\Parameters\EncodingParametersInterface;

interface RelationshipAdapterInterface
{

    /**
     * Set the field name that the relationship relates to.
     *
     * @param string $field
     * @return $this
     */
    public function withFieldName($field);

    /**
     * Query related resources for the specified domain record.
     *
     * For example, if a client was querying the `comments` relationship of a `posts` resource.
     * This method would be invoked providing the post that is being queried as the `$record` argument.
     *
     * @param object $record
     * @param EncodingParametersInterface $parameters
     * @return mixed
     */
    public function query($record, EncodingParametersInterface $parameters);

    /**
     * Query relationship data for the specified domain record.
     *
     * For example, if a client was querying the `comments` relationship of a `posts` resource.
     * This method would be invoked providing the post that is being queried as the `$record` argument.
     *
     * @param $record
     * @param EncodingParametersInterface $parameters
     * @return mixed
     */
    public function relationship($record, EncodingParametersInterface $parameters);

    /**
     * Update a domain record's relationship when filling a resource's relationships.
     *
     * For a has-one relationship, this changes the relationship to match the supplied relationship
     * object.
     *
     * For a has-many relationship, this completely replaces every member of the relationship, changing
     * it to match the supplied relationship object.
     *
     * @param object $record
     *      the object to hydrate.
     * @param RelationshipInterface $relationship
     *      the relationship object to use for the hydration.
     * @param EncodingParametersInterface $parameters
     * @return object
     *      the updated domain record.
     */
    public function update($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters);

    /**
     * Replace a domain record's relationship with data from the supplied relationship object.
     *
     * @param $record
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     * @return object
     *      the updated domain record.
     */
    public function replace($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters);

}
