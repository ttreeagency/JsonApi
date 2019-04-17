<?php

namespace Flowpack\JsonApi\Adapter;

use Flowpack\JsonApi\Contract\Object\RelationshipsInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Utility\Arrays;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Flowpack\JsonApi\Contract\JsonApiRepositoryInterface;
use Flowpack\JsonApi\Contract\Object\ResourceObjectInterface;
use Flowpack\JsonApi\Contract\Object\RelationshipInterface;
use Flowpack\JsonApi\Domain\BelongsTo;
use Flowpack\JsonApi\Domain\HasOne;
use Flowpack\JsonApi\Domain\HasMany;
use Flowpack\JsonApi\Domain\Model\Concern\ModelIncludesTrait;
use Flowpack\JsonApi\Domain\Model\PaginationParameters;
use Flowpack\JsonApi\Domain\Repository\DefaultRepository;
use Flowpack\JsonApi\Contract\Object\StandardObjectInterface;
use Flowpack\JsonApi\Encoder\Encoder;
use Flowpack\JsonApi\Exception;
use Flowpack\JsonApi\Exception\RuntimeException;
use Flowpack\JsonApi\Mvc\Controller\EncodingParametersParser;
use Flowpack\JsonApi\Utility\StringUtility as Str;
use Neos\Flow\Mvc\Controller\MvcPropertyMappingConfiguration;

/**
 * Class AbstractAdapter
 *
 * @package Flowpack\JsonApi
 *
 * @api
 */
abstract class AbstractAdapter extends AbstractResourceAdapter
{
    use ModelIncludesTrait;

    /**
     * @var string
     */
    protected $resource;

    /**
     * Short resource name
     * @var string
     */
    protected $entity;

    /**
     * @var string
     */
    protected $schema;

    /**
     * @var array
     */
    protected $related = [];

    /**
     * @var EncodingParametersParser
     */
    protected $parameters;

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject()
     * @var DefaultRepository
     */
    protected $repository;

    /**
     * @var ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var PaginationParameters|null
     */
    protected $paging;

    /**
     * @var array
     */
    protected $mapAttributeToProperty = [];

    /**
     * The model key that is the primary key for the resource id.
     *
     * If empty, defaults to `Model::getKeyName()`.
     *
     * @var string|null
     */
    protected $primaryKey;

    /**
     * The filter param for a find-many request.
     *
     * If null, defaults to the JSON API keyword `id`.
     *
     * @var string|null
     */
    protected $findManyFilter = null;

    /**
     * The default pagination to use if no page parameters have been provided.
     *
     * If your resource must always be paginated, use this to return the default
     * pagination variables... e.g. `['number' => 1]` for page 1.
     *
     * If this property is null or an empty array, then no pagination will be
     * used if no page parameters have been provided (i.e. every resource
     * will be returned).
     *
     * @var array|null
     */
    protected $defaultPagination = null;

    /**
     * The model relationships to eager load on every query.
     *
     * @var string[]|null
     * @deprecated use `$defaultWith` instead.
     */
    protected $with = null;

    /**
     * A mapping of sort parameters to columns.
     *
     * Use this to map any parameters to columns where the two are not identical. E.g. if
     * your sort param is called `sort` but the column to use is `type`, then set this
     * property to `['sort' => 'type']`.
     *
     * @var array
     */
    protected $sortColumns = [];

    /**
     * @param array $configuration
     * @param string $resource
     * @param EncodingParametersParser $parameters
     */
    public function __construct($configuration, $resource, EncodingParametersParser $parameters)
    {
        $this->configuration = $configuration;
        $this->resource = $resource;
        $this->parameters = $parameters;
    }

    /**
     * @throws Exception
     */
    protected function initializeObject()
    {
        $this->initializeConfiguration();
        $this->registerRepository();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function initializeConfiguration()
    {
        $this->entity = Arrays::getValueByPath($this->configuration, 'entity');
        if (!\is_string($this->entity)) {
            throw new Exception(\sprintf('Resource "%s" entity not found!', $this->resource), 1447947501);
        }
        $this->schema = Arrays::getValueByPath($this->configuration, 'schema');
        if (!\is_string($this->schema)) {
            throw new Exception(\sprintf('Resource "%s" schema not found!', $this->resource), 1447947502);
        }
        $this->related = Arrays::getValueByPath($this->configuration, 'related');
        if (!\is_array($this->related)) {
            throw new Exception(\sprintf('Resource "%s" related not configured', $this->resource), 1447947503);
        }
    }

    /**
     * @param string|null $urlPrefix
     * @param integer $depth
     * @return EncoderInterface
     */
    public function getEncoder($urlPrefix = null, $depth = 512)
    {
        return Encoder::instance($this->getResources())
            ->withUrlPrefix($urlPrefix)
            ->withEncodeDepth($depth)
            ->withEncodeOptions(JSON_PRETTY_PRINT);
    }

    /**
     * Get definition from this resource by combining entity and schema for both top level and sublevel related resources
     */
    protected function getResources()
    {
        $resources = [
            $this->entity => $this->schema
        ];

        foreach ($this->related as $resource) {
            $resources = \array_merge($resource, $resources);
        }
        return $resources;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns the Object Model based on resource configuration
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return object|JsonApiRepositoryInterface
     * @throws Exception
     */
    protected function registerRepository()
    {
        $repository = $this->objectManager->get('Flowpack\JsonApi\Domain\Repository\DefaultRepository');
        if (isset($this->configuration['repository'])) {
            $repository = $this->objectManager->get($this->configuration['repository']);
        }

        if (!isset($this->configuration['entity'])) {
            throw new Exception(sprintf('Resource "%s" no "entity" configured', $this->resource), 1447947510);
        }

        $repository->setEntityClassName($this->configuration['entity']);
        $this->model = $repository->getEntityClassName();
        $this->repository = $repository;
    }

    /**
     * @param \Neos\Flow\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration
     */
    public function setPropertyMappingConfiguration(\Neos\Flow\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration, ResourceObjectInterface $resource)
    {
        $propertyMappingConfiguration->setTypeConverterOption('Neos\Flow\Property\TypeConverter\PersistentObjectConverter', \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
        $propertyMappingConfiguration->setTypeConverterOption('Neos\Flow\Property\TypeConverter\PersistentObjectConverter', \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, true);
        $propertyMappingConfiguration->allowAllProperties();

        if ($resource->hasRelationships()) {
            /** @var RelationshipInterface $relationship */
            foreach ($resource->getRelationships()->getAll() as $field => $value) {

                if (!$this->isRelation($field)) {
                    continue;
                }


                if (!$method = $this->methodForRelation($field)) {
                    throw new RuntimeException("No relationship method implemented for field {$field}.");
                }

                $relation = $this->{$method}();

                if ($relation instanceof AbstractManyRelation) {
                    $propertyMappingConfiguration->forProperty($field . '.*')
                        ->setTypeConverterOption(
                            \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::class,
                            \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
                            true
                        )
                        ->setTypeConverterOption(
                            \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::class,
                            \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED,
                            true
                        )
                        ->allowAllProperties();
                    continue;
                }

                $propertyMappingConfiguration->forProperty($field)
                    ->setTypeConverterOption(
                        \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::class,
                        \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
                        true
                    )
                    ->setTypeConverterOption(
                        \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::class,
                        \Neos\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED,
                        true
                    )
                    ->allowAllProperties();
            }
        }
    }

    /**
     * Apply the supplied filters to the builder instance.
     *
     * @param QueryInterface $query
     * @param array|null $filters
     * @return void
     */
    abstract protected function filter($query, $filters);

    /**
     * @param EncodingParametersParser $parameters
     * @return mixed|PageInterface
     * @throws RuntimeException
     */
    public function query(EncodingParametersParser $parameters)
    {
        $filters = $this->extractFilters($parameters);
        $query = $this->newQuery();

        /** Apply eager loading */
        $this->with($query, $this->extractIncludePaths($parameters));

        /** Filter and sort */
        $this->filter($query, $filters);

        $this->sort($query, $parameters->getSorts());

        /** Paginate results if needed. */
        $pagination = $this->extractPagination($parameters);

//        if (!$pagination->isEmpty() && !$this->hasPaging()) {
//            throw new RuntimeException('Paging parameters exist but paging is not supported.');
//        }

        return $this->all($query);
//        return $pagination->isEmpty() ?
//            $this->all($query) :
//            $this->paginate($query, $this->normalizeParameters($parameters, $pagination));
    }

    /**
     * @param EncodingParametersParser $parameters
     * @return mixed
     * @throws RuntimeException
     */
    public function count(EncodingParametersParser $parameters)
    {
        $filters = $this->extractFilters($parameters);
        $query = $this->newQuery();

        /** Filter and sort */
        $this->filter($query, $filters);

        return $this->countAll($query);
    }

    /**
     * Query the resource when it appears in a relation of a parent model.
     *
     * For example, a request to `/posts/1/comments` will invoke this method on the
     * comments adapter.
     *
     * @param Relations\BelongsToMany|Relations\HasMany|Relations\HasManyThrough $relation
     * @param EncodingParametersParser $parameters
     * @return mixed
     */
    public function queryRelation($relation, EncodingParametersParser $parameters)
    {
        $query = $relation->newQuery();

        /** Apply eager loading */
        $this->with($query, $this->extractIncludePaths($parameters));

        /** Filter and sort */
        $this->filter($query, $this->extractFilters($parameters));
        $this->sort($query, (array)$parameters->getSorts());

        /** Paginate results if needed. */
        $pagination = $parameters->getPagination();

        if (!$pagination->isEmpty() && !$this->hasPaging()) {
            throw new RuntimeException('Paging parameters exist but paging is not supported.');
        }

        return $pagination->isEmpty() ?
            $this->all($query) :
            $this->paginate($query, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function read($resourceId, EncodingParametersParser $parameters)
    {
        if ($record = parent::read($resourceId, $parameters)) {
            $this->load($record, $parameters);
        }

        return $record;
    }

    /**
     * @inheritdoc
     */
    public function update($record, ResourceObjectInterface $resource, EncodingParametersParser $parameters)
    {
        /** @var object $record */
        $record = parent::update($record, $resource, $parameters);

        $this->load($record, $parameters);

        return $record;
    }

    /**
     * @param $record
     * @param EncodingParametersParser $params
     * @return bool|void
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function delete($record, EncodingParametersParser $params)
    {
        $this->repository->remove($record);
    }

    /**
     * @inheritDoc
     */
    public function exists($resourceId)
    {
        return $this->newQuery()->where($this->getQualifiedKeyName(), $resourceId)->exists();
    }

    /**
     * @inheritDoc
     */
    public function find($resourceId)
    {
        return $this->repository->findByIdentifier($resourceId);
    }

    /**
     * @inheritDoc
     */
    public function findMany(array $resourceIds)
    {
        return $this->newQuery()->whereIn($this->getQualifiedKeyName(), $resourceIds)->get()->all();
    }

    /**
     * Get a new query builder.
     *
     * Child classes can overload this method if they want to modify the query instance that
     * is used for every query the adapter does.
     *
     * @return QueryInterface
     */
    protected function newQuery()
    {
        return $this->repository->createQuery();
    }

    /**
     * @todo Optimalization for better performance
     * Add eager loading to the query.
     *
     * @param QueryInterface $query
     * @param array $includePaths
     *      the paths for resources that will be included.
     * @return void
     */
    protected function with($query, $includePaths)
    {
//        $query->setFetchMode($this->getRelationshipPaths($includePaths));
        return;
    }

    /**
     * Add eager loading to a record.
     *
     * @param $record
     * @param EncodingParametersParser $parameters
     */
    protected function load($record, EncodingParametersParser $parameters)
    {
        $relationshipPaths = $this->getRelationshipPaths($this->extractIncludePaths($parameters));

        /** Eager load anything that needs to be loaded. */
        if (\method_exists($record, 'loadMissing')) {
            $record->loadMissing($relationshipPaths);
        }
    }

    /**
     * @inheritDoc
     */
    protected function createRecord(ResourceObjectInterface $resource)
    {
        return (new $this->model());
    }

    /**
     * @todo
     * @inheritDoc
     */
    public function hydrateAttributes($record, StandardObjectInterface $attributes)
    {
        $arguments = [];

        foreach ($attributes->toArray() as $propertyName => $propertyValue) {
            if (\array_key_exists($propertyName, $this->mapAttributeToProperty)) {
                $arguments[$this->mapAttributeToProperty[$propertyName]] = $propertyValue;
                continue;
            }

            $arguments[Str::camelize($propertyName)] = $propertyValue;
        }

        if ($record->hasId()) {
            $arguments['__identity'] = $record->getId();
        }

        return $arguments;
    }

    /**
     * @param $record
     * @param RelationshipsInterface $relationships
     * @return array
     * @throws RuntimeException
     */
    public function hydrateRelations($record, RelationshipsInterface $relationships)
    {
        $arguments = [];

        if ($relationships !== null) {
            foreach ($relationships->getAll() as $field => $value) {
                if (!$this->isRelation($field)) {
                    continue;
                }

                $relation = $this->related($field);
                $relationship = $relationships->getRelationship($field);
                $key = $relation->getKey();

                $relationshipObjectCollection = $relationship->getRelationCollection();
                if (!empty($relationshipObjectCollection)) {

                    /** @var RelationshipObject $relationshipObject */
                    foreach ($relationshipObjectCollection as $relationshipObject) {
                        $entry = [];
                        if ($relationshipObject->hasAttributes()) {
                            $entry = $relationshipObject->getAttributes()->toArray();
                        }
                        if ($relationship->hasIdentifier() && $relationship->getIdentifier()->hasId()) {
                            $entry['__identity'] = (string)$relationship->getIdentifier()->getId();
                        }
                        $arguments[$key][] = $entry;
                    }
                } else {
                    if ($relationship->hasIdentifier() && $relationship->getIdentifier()->hasId()) {
                        $arguments[$key]['__identity'] = (string)$relationship->getIdentifier()->getId();
                    }
                }
                // TODO: $this->requiresPrimaryRecordPersistence($relation)
            }
        }
        return $arguments;
    }

    /**
     * @inheritdoc
     */
    protected function fillRelationship(
        $record,
        $field,
        RelationshipInterface $relationship,
        EncodingParametersParser $parameters
    )
    {
        $relation = $this->related($field);
        if (!$this->requiresPrimaryRecordPersistence($relation)) {
            $relation->update($record, $relationship, $parameters);
        }
    }

    /**
     * @todo
     * Hydrate related models after the primary record has been persisted.
     *
     * @param object $record
     * @param ResourceObjectInterface $resource
     * @param EncodingParametersParser $parameters
     */
    protected function hydrateRelated(
        $record,
        ResourceObjectInterface $resource,
        EncodingParametersParser $parameters
    )
    {
        $relationships = $resource->getRelationships();
        $changed = false;

        if ($relationships !== null) {

            foreach ($relationships->getAll() as $field => $value) {
//            /** Skip any fields that are not fillable. */
//            if ($this->isNotFillable($field, $record)) {
//                continue;
//            }

                /** Skip any fields that are not relations */
                if (!$this->isRelation($field)) {
                    continue;
                }

                $relation = $this->related($field);

                if ($this->requiresPrimaryRecordPersistence($relation)) {
                    $relation->update($record, $relationships->getRelationship($field), $parameters);
                    $changed = true;
                }
            }
        }


//        /** If there are changes, we need to refresh the model in-case the relationship has been cached. */
        if ($changed) {
            $this->persist($record);
        }
    }

    /**
     * @todo
     * Does the relationship need to be hydrated after the primary record has been persisted?
     *
     * @param RelationshipAdapterInterface $relation
     * @return bool
     */
    protected function requiresPrimaryRecordPersistence($relation)
    {
        return $relation instanceof HasManyAdapterInterface || $relation instanceof HasOne;
    }

    /**
     * @param $record
     * @return object
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    protected function persist($record)
    {
        if ($this->persistenceManager->isNewObject($record)) {
            $this->repository->add($record);
        } else {
            $this->repository->update($record);
        }

        $this->persistenceManager->persistAll();
        return $record;
    }

    /**
     * @todo
     * @param QueryInterface $query
     * @param Collection $filters
     * @return mixed
     */
    protected function findByIds($query, $filters)
    {
        return $query
            ->whereIn($this->getQualifiedKeyName(), $this->extractIds($filters))
            ->get();
    }

    /**
     * Return the result for a search one query.
     *
     * @param QueryInterface $query
     * @return object
     */
    protected function first($query)
    {
        return $query->execute()->getFirst();
    }

    /**
     * Return the result for query that is not paginated.
     *
     * @param QueryInterface $query
     * @return mixed
     */
    protected function all($query)
    {
        return $query->execute();
    }

    /**
     * Return the count of the query
     * @param $query
     * @return mixed
     */
    protected function countAll($query)
    {
        return $query->execute()->count();
    }

    /**
     * @todo
     * Return the result for a paginated query.
     *
     * @param QueryInterface $query
     * @param EncodingParametersParser $parameters
     * @return PageInterface
     */
    protected function paginate($query, EncodingParametersParser $parameters)
    {
        return $this->paging->paginate($query, $parameters);
    }

    /**
     * @todo
     * Get the key that is used for the resource ID.
     *
     * @return string
     */
    protected function getKeyName()
    {
        return $this->primaryKey ?: $this->model->getKeyName();
    }

    /**
     * @todo
     * @return string
     */
    protected function getQualifiedKeyName()
    {
        return \sprintf('%s.%s', $this->model->getTable(), $this->getKeyName());
    }

    /**
     * @param EncodingParametersParser $parameters
     * @return array
     */
    protected function extractIncludePaths(EncodingParametersParser $parameters)
    {
        return $parameters->getIncludes();
    }

    /**
     * @param EncodingParametersParser $parameters
     * @return array
     */
    protected function extractFilters(EncodingParametersParser $parameters)
    {
        return $parameters->getFilters();
    }

    /**
     * @param EncodingParametersParser $parameters
     * @return array
     */
    protected function extractPagination(EncodingParametersParser $parameters)
    {
        $pagination = (array)$parameters->getPagination();

        return $pagination ?: $this->defaultPagination();
    }

    /**
     * @return array
     */
    protected function defaultPagination()
    {
        return (array)$this->defaultPagination;
    }

    /**
     * @return bool
     */
    protected function hasPaging()
    {
        return $this->paging instanceof PaginationParameters;
    }

    /**
     * Apply sort parameters to the query.
     *
     * @param QueryInterface $query
     * @param array $sortBy
     * @return void
     */
    protected function sort($query, $sortBy)
    {
        if (empty($sortBy)) {
            $this->defaultSort($query);
            return;
        }

        $ordering = [];
        foreach ($sortBy as $property => $param) {
            $ordering = \array_merge($ordering, $this->sortBy($query, $property, $param));
        }

        $query->setOrderings($ordering);
    }

    /**
     * Apply a default sort order if the client has not requested any sort order.
     *
     * Child classes can override this method if they want to implement their
     * own default sort order.
     *
     * @param QueryInterface $query
     * @return void
     */
    protected function defaultSort($query)
    {
    }

    /**
     * @param QueryInterface $query
     * @param string $property
     * @param bool $param
     * @return array
     */
    protected function sortBy($query, $property, $param)
    {
        $column = $this->getQualifiedSortColumn($query, $property);

        return [$column => $param === true ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING];
    }

    /**
     * @param QueryInterface $query
     * @param string $field
     * @return string
     */
    protected function getQualifiedSortColumn($query, $field)
    {
        $key = $this->columnForField($field, $query->getType());

        return $key;
    }

    /**
     * @todo
     * Get the table column to use for the specified search field.
     *
     * @param string $field
     * @param string $entity
     * @return string
     */
    protected function columnForField($field, $entity)
    {
        /** If there is a custom mapping, return that */
        if (isset($this->sortColumns[$field])) {
            return $this->sortColumns[$field];
        }

//        if (\strpos($field, '.')) {
//            $relation = \strtok($field, '.');
//
//            if (\method_exists($entity, $relation)) {
//                /** @var Relations\Relation $relationShip */
//                $relationShip = $model->$relation();
//                $table = $relationShip->getRelated()->getTable();
//
//                if (($position = \strpos($field, '.')) !== false) {
//                    $tableWithField = $table . \substr($field, $position);
//                    return $model::$snakeAttributes ? Str::underscore($tableWithField) : Str::camelize($tableWithField);
//                }
//            }
//        }

        return Str::camelize($field);
    }

    /**
     * @param string|null $modelKey
     * @return BelongsTo
     */
    protected function belongsTo($modelKey = null)
    {
        return new BelongsTo($this->model, $modelKey ?: $this->guessRelation());
    }

    /**
     * @param string|null $modelKey
     * @return HasOne
     */
    protected function hasOne($modelKey = null)
    {
        return new HasOne($this->model, $modelKey ?: $this->guessRelation());
    }

    /**
     * @param string|null $modelKey
     * @return HasMany
     */
    protected function hasMany($modelKey = null)
    {
        return new HasMany($this->model, $modelKey ?: $this->guessRelation());
    }

    /**
     * @return string
     */
    protected function guessRelation()
    {
        list($one, $two, $caller) = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        return $caller['function'];
    }

    /**
     * Return the Property Mapping Configuration used for this argument; can be used by the initialize*action to modify the Property Mapping.
     *
     * @return MvcPropertyMappingConfiguration
     * @api
     */
    public function getPropertyMappingConfiguration()
    {
        if ($this->propertyMappingConfiguration === null) {
            $this->propertyMappingConfiguration = new MvcPropertyMappingConfiguration();
        }
        return $this->propertyMappingConfiguration;
    }
}
