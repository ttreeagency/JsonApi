<?php

namespace Flowpack\JsonApi\Domain;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping\Entity;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Flowpack\JsonApi\Exception\RuntimeException;
use Flowpack\JsonApi\Contract\Adapter\RelationshipAdapterInterface;

/**
 * Class AbstractRelation
 *
 */
abstract class AbstractRelation implements RelationshipAdapterInterface
{
    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * @var object
     */
    protected $model;

    /**
     * The model key.
     *
     * @var string
     */
    protected $key;

    /**
     * @var string|null
     */
    protected $field;

    /**
     * Is the supplied Doctrine relation acceptable for this JSON API relation?
     *
     * @param Relation $relation
     * @return bool
     */
    abstract protected function acceptRelation($relation);

    /**
     * AbstractRelation constructor.
     *
     * @param $model
     * @param $key
     */
    public function __construct($model, $key)
    {
        $this->model = $model;
        $this->key = $key;
    }

    /**
     * @return object
     */
    public function getModel(): object
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return null|string
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @inheritdoc
     */
    public function withFieldName($name)
    {
        $this->field = $name;

        return $this;
    }

    /**
     * @param $record
     * @return Relation
     * @todo
     * Get the relation from the model.
     *
     */
    protected function getRelation($record)
    {
        $relation = $record->{'get' . \ucfirst($this->key)}();
        return $relation;
    }

}
