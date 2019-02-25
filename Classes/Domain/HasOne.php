<?php

namespace Ttree\JsonApi\Domain;

use Ttree\JsonApi\Contract\Object\RelationshipInterface;
use Ttree\JsonApi\Contract\Parameters\EncodingParametersInterface;

class HasOne extends BelongsTo
{

    /**
     * @inheritDoc
     */
    public function update($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters)
    {
        $relation = $this->getRelation($record);
        $related = $this->related($relationship);
        /** @var Model|null $current */
        $current = $record->{$this->key};

        /** If the relationship is not changing, we do not need to do anything. */
        if ($current && $related && $current->is($related)) {
            return;
        }

        /** If there is a current related model, we need to clear it. */
        if ($current) {
            $current->setAttribute($relation->getForeignKeyName(), null)->save();
        }

        /** If there is a related model, save it. */
        if ($related) {
            $relation->save($related);
        }

        // no need to refresh $record as the Doctrine adapter will do it.
    }

    /**
     * @inheritDoc
     */
    public function replace($record, RelationshipInterface $relationship, EncodingParametersInterface $parameters)
    {
        $this->update($record, $relationship, $parameters);
        $record->refresh(); // in case the relationship has been cached.

        return $record;
    }

    /**
     * @inheritdoc
     */
    protected function acceptRelation($relation)
    {
        return $relation instanceof HasOne;
    }
}
