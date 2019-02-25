<?php

namespace Ttree\JsonApi\Domain;

use Ttree\JsonApi\Contract\Object\RelationshipInterface;
use Ttree\JsonApi\Contract\Parameters\EncodingParametersInterface;

/**
 * Class HasOne
 *
 * @package CloudCreativity\LaravelJsonApi
 */
class BelongsTo extends AbstractRelation
{

    /**
     * @param Model $record
     * @param EncodingParametersInterface $parameters
     * @return mixed
     */
    public function query($record, EncodingParametersInterface $parameters)
    {
        return $record->{$this->key};
    }

    /**
     * @param Model $record
     * @param EncodingParametersInterface $parameters
     * @return mixed
     */
    public function relationship($record, EncodingParametersInterface $parameters)
    {
        return $this->query($record, $parameters);
    }

    /**
     * @param $record
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     * @return void
     */
    public function update($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters)
    {
        $relation = $this->getRelation($record);
        // TODO: associate and dissociate are only possible if object is found
return;

        if ($related = $this->findRelated($relationship)) {
            $relation->associate($related);
        } else {
            $relation->dissociate();
        }
    }

    /**
     * @param $record
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     * @return
     */
    public function replace($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters)
    {
        $this->update($record, $relationship, $parameters);
        $record->save();

        return $record;
    }

    /**
     * @inheritdoc
     */
    protected function acceptRelation($relation)
    {
        return $relation instanceof BelongsTo;
    }

    /**
     * Find the related model for the JSON API relationship.
     *
     * @param RelationshipInterface $relationship
     * @return null
     */
    protected function findRelated(RelationshipInterface $relationship)
    {
        $identifier = $relationship->hasIdentifier() ? $relationship->getIdentifier() : null;

        // TODO: We need the class name to be able to find the object
//        $this->persistenceManager->getObjectByIdentifier($identifier->getId());

        return $identifier ? null : null;
    }

    /**
     * @todo
     * Get the relation from the model.
     *
     * @param $record
     * @return null|object
     */
    protected function getRelation($record)
    {
        if (\method_exists($record, $method = 'get' . \ucfirst($this->key))) {
            return $record->$method();
        }
        return null;
    }
}
