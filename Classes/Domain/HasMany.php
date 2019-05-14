<?php

namespace Flowpack\JsonApi\Domain;

use Flowpack\JsonApi\Contract\Object\RelationshipInterface;
use Flowpack\JsonApi\Contract\Parameters\EncodingParametersInterface;
/**
 * @todo this does important stuff in laravel but for Doctrine this part seems not interesting so far.
 * Class HasMany
 *
 * @package CloudCreativity\LaravelJsonApi
 */
class HasMany extends AbstractManyRelation
{

    /**
     * @param Model $record
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     * @return void
     */
    public function update($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters)
    {
        $related = $this->findRelated($record, $relationship);
        $relation = $this->getRelation($record);
        // do not refresh as we expect the resource adapter to refresh the record.
    }

    /**
     * @param Model $record
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     * @return Model
     */
    public function replace($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters)
    {
        $this->update($record, $relationship, $parameters);
        $record->refresh(); // in case the relationship has been cached.

        return $record;
    }

    /**
     * @param Model $record
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     * @return Model
     */
    public function add($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters)
    {
        $related = $this->findRelated($record, $relationship);

        $this->getRelation($record)->saveMany($related);
        $record->refresh(); // in case the relationship has been cached.

        return $record;
    }

    /**
     * @param Model $record
     * @param RelationshipInterface $relationship
     * @param EncodingParametersInterface $parameters
     * @return Model
     */
    public function remove($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters)
    {
        $related = $this->findRelated($record, $relationship);
        $relation = $this->getRelation($record);

        if ($relation instanceof Relations\BelongsToMany) {
            $relation->detach($related);
        } else {
            $this->detach($relation, $related);
        }

        $record->refresh(); // in case the relationship has been cached

        return $record;
    }

    /**
     * @inheritdoc
     */
    protected function acceptRelation($relation)
    {
        return $relation instanceof Relations\BelongsToMany ||
            $relation instanceof Relations\HasMany ||
            $relation instanceof Relations\MorphMany;
    }

    /**
     * @param Relations\HasMany $relation
     * @param Collection $existing
     * @param $updated
     */
    protected function sync(Relations\HasMany $relation, Collection $existing, Collection $updated)
    {
        $add = collect($updated)->reject(function ($model) use ($existing) {
            return $existing->contains($model);
        });

        $remove = $existing->reject(function ($model) use ($updated) {
            return $updated->contains($model);
        });

        if ($remove->isNotEmpty()) {
            $this->detach($relation, $remove);
        }

        $relation->saveMany($add);
    }

    /**
     * @param Relations\HasMany $relation
     * @param Collection $remove
     */
    protected function detach(Relations\HasMany $relation, Collection $remove)
    {
        /** @var Model $model */
        foreach ($remove as $model) {
            $model->setAttribute($relation->getForeignKeyName(), null)->save();
        }
    }

    /**
     * Find the related models for a JSON API relationship object.
     *
     * We look up the models via the store. These then have to be filtered to
     * ensure they are of the correct model type, because this has-many relation
     * might be used in a polymorphic has-many JSON API relation.
     *
     * @todo this is currently inefficient for polymorphic relationships. We
     * need to be able to filter the resource identifiers by the expected resource
     * type before looking them up via the store.
     *
     * @param $record
     * @param RelationshipInterface $relationship
     * @return Collection
     */
    protected function findRelated($record, RelationshipInterface $relationship)
    {
        return $record->{'get' . \ucfirst($this->key)}();
    }

}
