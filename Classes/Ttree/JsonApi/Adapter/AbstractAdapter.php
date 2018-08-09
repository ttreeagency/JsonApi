<?php

namespace Ttree\JsonApi\Adapter;

//use CloudCreativity\LaravelJsonApi\Contracts\Adapter\HasManyAdapterInterface;
//use CloudCreativity\LaravelJsonApi\Contracts\Adapter\RelationshipAdapterInterface;
//use CloudCreativity\LaravelJsonApi\Contracts\Object\RelationshipInterface;
//use CloudCreativity\LaravelJsonApi\Contracts\Object\ResourceObjectInterface;
//use CloudCreativity\LaravelJsonApi\Contracts\Pagination\PageInterface;
//use CloudCreativity\LaravelJsonApi\Contracts\Pagination\PagingStrategyInterface;
//use Illuminate\Support\Collection;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neos\Flow\Annotations as Flow;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\SortParameterInterface;
use Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Utility\Arrays;
use Neos\Utility\ObjectAccess;
use Ttree\JsonApi\Contract\Object\ResourceObjectInterface;
use Ttree\JsonApi\Contract\Object\RelationshipInterface;
use Ttree\JsonApi\Domain\Model\Concern\DeserializesAttributeTrait;
use Ttree\JsonApi\Domain\Repository\DefaultRepository;
use Ttree\JsonApi\Contract\Object\StandardObjectInterface;
use Ttree\JsonApi\Encoder\Encoder;
use Ttree\JsonApi\Exception;
use Ttree\JsonApi\Exception\RuntimeException;

/**
 * Class AbstractAdapter
 *
 * @package Ttree\JsonApi
 *
 * @api
 */
abstract class AbstractAdapter extends AbstractResourceAdapter
{
    use DeserializesAttributeTrait;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var EncodingParametersInterface
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
     * @Flow\InjectConfiguration(path="endpoints.default")
     */
    protected $settings;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var Object
     */
    protected $model;

    /**
     * @var PagingStrategyInterface|null
     */
    protected $paging;

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
     * @param string $resource
     * @param EncodingParametersInterface $parameters
     */
    public function __construct($resource, EncodingParametersInterface $parameters)
    {
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
        $configuration = Arrays::getValueByPath($this->settings, ['resources', $this->resource]);
        if (!\is_array($configuration)) {
            throw new Exception(\sprintf('Resource "%s" not configured', $this->resource), 1447947509);
        }
        $this->configuration = $configuration;
    }

    /**
     * @param string|null $urlPrefix
     * @param integer $depth
     * @return EncoderInterface
     */
    public function getEncoder($urlPrefix = null, $depth = 512)
    {
        return Encoder::instance($this->configuration['schemas'], new EncoderOptions(JSON_PRETTY_PRINT, $urlPrefix, $depth));
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return isset($this->settings['baseUrl']) ? $this->settings['baseUrl'] : '/';
    }

    /**
     * @return object|JsonApiRepositoryInterface
     * @throws Exception
     */
    protected function registerRepository()
    {
        $repository = $this->objectManager->get('Ttree\JsonApi\Domain\Repository\DefaultRepository');
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
     * Apply the supplied filters to the builder instance.
     *
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    abstract protected function filter($query, Collection $filters);

    /**
     * @param EncodingParametersInterface $parameters
     * @return mixed|PageInterface
     * @throws RuntimeException
     */
    public function query(EncodingParametersInterface $parameters)
    {
        $filters = $this->extractFilters($parameters);
        $query = $this->newQuery();

//        /** Apply eager loading */
//        $this->with($query, $this->extractIncludePaths($parameters));
//
//        /** Find by ids */
//        if ($this->isFindMany($filters)) {
//            return $this->findByIds($query, $filters);
//        }
//
//        /** Filter and sort */
//        $this->filter($query, $filters);
//
//        $this->sort($query, (array) $parameters->getSortParameters());
//
//        /** Return a single record if this is a search for one resource. */
//        if ($this->isSearchOne($filters)) {
//            return $this->first($query);
//        }

        /** Paginate results if needed. */
        $pagination = $this->extractPagination($parameters);

//        if (!$pagination->isEmpty() && !$this->hasPaging()) {
//            throw new RuntimeException('Paging parameters exist but paging is not supported.');
//        }

//        return $pagination->isEmpty() ?
//            $this->all($query) :
//            $this->paginate($query, $this->normalizeParameters($parameters, $pagination));
        return $this->all($query);
    }

    /**
     * Query the resource when it appears in a relation of a parent model.
     *
     * For example, a request to `/posts/1/comments` will invoke this method on the
     * comments adapter.
     *
     * @param Relations\BelongsToMany|Relations\HasMany|Relations\HasManyThrough $relation
     * @param EncodingParametersInterface $parameters
     * @return mixed
     * @todo this does not currently support default pagination as it causes a problem with polymorphic relations
     */
    public function queryRelation($relation, EncodingParametersInterface $parameters)
    {
        $query = $relation->newQuery();

        /** Apply eager loading */
        $this->with($query, $this->extractIncludePaths($parameters));

        /** Filter and sort */
        $this->filter($query, $this->extractFilters($parameters));
        $this->sort($query, (array)$parameters->getSortParameters());

        /** Paginate results if needed. */
        $pagination = collect($parameters->getPaginationParameters());

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
    public function read($resourceId, EncodingParametersInterface $parameters)
    {
        if ($record = parent::read($resourceId, $parameters)) {
            $this->load($record, $parameters);
        }

        return $record;
    }

    /**
     * @inheritdoc
     */
    public function update($record, ResourceObjectInterface $resource, EncodingParametersInterface $parameters)
    {
        /** @var Model $record */
        $record = parent::update($record, $resource, $parameters);
        $this->load($record, $parameters);

        return $record;
    }

    /**
     * @param $record
     * @param EncodingParametersInterface $params
     * @return bool|void
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function delete($record, EncodingParametersInterface $params)
    {
        $this->repository->remove($record);
    }

    /**
     * @inheritDoc
     */
    public function exists($resourceId)
    {
//        return $this->newQuery()->where($this->getQualifiedKeyName(), $resourceId)->exists();
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
//        return $this->newQuery()->whereIn($this->getQualifiedKeyName(), $resourceIds)->get()->all();
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
     * Add eager loading to the query.
     *
     * @param Builder $query
     * @param Collection $includePaths
     *      the paths for resources that will be included.
     * @return void
     */
    protected function with($query, $includePaths)
    {
        $query->with($this->getRelationshipPaths($includePaths));
    }

    /**
     * @todo eager load the includedpaths and relationship paths
     * Add eager loading to a record.
     *
     * @param $record
     * @param EncodingParametersInterface $parameters
     */
    protected function load($record, EncodingParametersInterface $parameters)
    {
//        $relationshipPaths = $this->getRelationshipPaths($this->extractIncludePaths($parameters));

//        /** Eager load anything that needs to be loaded. */
//        if (method_exists($record, 'loadMissing')) {
//            $record->loadMissing($relationshipPaths);
//        }
    }

    /**
     * @inheritDoc
     */
    protected function createRecord(ResourceObjectInterface $resource)
    {
        return new $this->model();
    }

    /**
     * @inheritDoc
     */
    protected function hydrateAttributes($record, StandardObjectInterface $attributes)
    {
        $data = [];

        foreach ($attributes as $field => $value) {
            /** Skip any JSON API fields that are not to be filled. */
            // TODO: Check if fields are prohibitated
//            if ($this->isNotFillable($field, $record)) {
//                continue;
//            }


            $key = $this->keyForAttribute($field, $record);
            ObjectAccess::setProperty($record, $key, $this->deserializeAttribute($value, $field, $attributes), true);
        }
    }

    /**
     * Convert a JSON API attribute key into a model attribute key.
     *
     * @param $resourceKey
     * @param $model
     * @return string
     * @deprecated use `modelKeyForField`
     */
    protected function keyForAttribute($resourceKey, $model)
    {
        return $this->modelKeyForField($resourceKey, $model);
    }

    /**
     * @inheritdoc
     */
    protected function fillRelationship(
        $record,
        $field,
        RelationshipInterface $relationship,
        EncodingParametersInterface $parameters
    )
    {
        $relation = $this->related($field);

        if (!$this->requiresPrimaryRecordPersistence($relation)) {
            $relation->update($record, $relationship, $parameters);
        }
    }

    /**
     * Hydrate related models after the primary record has been persisted.
     *
     * @param Model $record
     * @param ResourceObjectInterface $resource
     * @param EncodingParametersInterface $parameters
     */
    protected function hydrateRelated(
        $record,
        ResourceObjectInterface $resource,
        EncodingParametersInterface $parameters
    )
    {
        $relationships = $resource->getRelationships();
        $changed = false;

        foreach ($relationships->getAll() as $field => $value) {
            /** Skip any fields that are not fillable. */
            if ($this->isNotFillable($field, $record)) {
                continue;
            }

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

        /** If there are changes, we need to refresh the model in-case the relationship has been cached. */
        if ($changed) {
            $record->refresh();
        }
    }

    /**
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

        return $record;
    }

    /**
     * @param Builder $query
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
        return $query->first();
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
     * Is this a search for a singleton resource?
     *
     * @param Collection $filters
     * @return bool
     */
    protected function isSearchOne($filters)
    {
        return false;
    }

    /**
     * @todo
     * Return the result for a paginated query.
     *
     * @param QueryInterface $query
     * @param EncodingParametersInterface $parameters
     * @return PageInterface
     */
    protected function paginate($query, EncodingParametersInterface $parameters)
    {
        return $this->paging->paginate($query, $parameters);
    }

    /**
     * Get the key that is used for the resource ID.
     *
     * @return string
     */
    protected function getKeyName()
    {
        return $this->primaryKey ?: $this->model->getKeyName();
    }

    /**
     * @return string
     */
    protected function getQualifiedKeyName()
    {
        return sprintf('%s.%s', $this->model->getTable(), $this->getKeyName());
    }

    /**
     * @param EncodingParametersInterface $parameters
     * @return Collection
     */
    protected function extractIncludePaths(EncodingParametersInterface $parameters)
    {
//        return collect($parameters->getIncludePaths());
    }

    /**
     * @param EncodingParametersInterface $parameters
     * @return Collection
     */
    protected function extractFilters(EncodingParametersInterface $parameters)
    {
//        return collect($parameters->getFilteringParameters());
    }

    /**
     * @param EncodingParametersInterface $parameters
     * @return Collection
     */
    protected function extractPagination(EncodingParametersInterface $parameters)
    {
        $pagination = (array)$parameters->getPaginationParameters();

//        return collect($pagination ?: $this->defaultPagination());
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
//        return $this->paging instanceof PagingStrategyInterface;
    }

    /**
     * Apply sort parameters to the query.
     *
     * @param Builder $query
     * @param SortParameterInterface[] $sortBy
     * @return void
     */
    protected function sort($query, array $sortBy)
    {
        if (empty($sortBy)) {
            $this->defaultSort($query);
            return;
        }

        /** @var SortParameterInterface $param */
        foreach ($sortBy as $param) {
            $this->sortBy($query, $param);
        }
    }

    /**
     * Apply a default sort order if the client has not requested any sort order.
     *
     * Child classes can override this method if they want to implement their
     * own default sort order.
     *
     * @param Builder $query
     * @return void
     */
    protected function defaultSort($query)
    {
    }

    /**
     * @param Builder $query
     * @param SortParameterInterface $param
     */
    protected function sortBy($query, SortParameterInterface $param)
    {
        $column = $this->getQualifiedSortColumn($query, $param->getField());
        $order = $param->isAscending() ? 'asc' : 'desc';

        $query->orderBy($column, $order);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @return string
     */
    protected function getQualifiedSortColumn($query, $field)
    {
        $key = $this->columnForField($field, $query->getModel());

//        if (!\str_contains($key, '.')) {
//            $key = sprintf('%s.%s', $query->getModel()->getTable(), $key);
//        }

        return $key;
    }

    /**
     * Get the table column to use for the specified search field.
     *
     * @param string $field
     * @param Model $model
     * @return string
     */
    protected function columnForField($field, Model $model)
    {
        /** If there is a custom mapping, return that */
        if (isset($this->sortColumns[$field])) {
            return $this->sortColumns[$field];
        }

        if (\strpos($field, '.')) {
            $relation = \strtok($field, '.');

            if (\method_exists($model, $relation)) {
                /** @var Relations\Relation $relationShip */
                $relationShip = $model->$relation();
                $table = $relationShip->getRelated()->getTable();

                if (($position = \strpos($field, '.')) !== false) {
                    $tableWithField = $table . \substr($field, $position);
                    return $model::$snakeAttributes ? Str::underscore($tableWithField) : Str::camelize($tableWithField);
                }
            }
        }

        return $model::$snakeAttributes ? Str::underscore($field) : Str::camelize($field);
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
     * @param string|null $modelKey
     * @return HasManyThrough
     */
    protected function hasManyThrough($modelKey = null)
    {
        return new HasManyThrough($this->model, $modelKey ?: $this->guessRelation());
    }

    /**
     * @param HasManyAdapterInterface ...$adapters
     * @return MorphHasMany
     */
    protected function morphMany(HasManyAdapterInterface ...$adapters)
    {
        return new MorphHasMany(...$adapters);
    }

    /**
     * Normalize parameters for pagination.
     *
     *
     * @param EncodingParametersInterface $parameters
     * @param Collection $extractedPagination
     * @return EncodingParameters
     */
    protected function normalizeParameters(EncodingParametersInterface $parameters, Collection $extractedPagination)
    {
        return new EncodingParameters(
            $parameters->getIncludePaths(),
            $parameters->getFieldSets(),
            $parameters->getSortParameters(),
            $extractedPagination->all(),
            $parameters->getFilteringParameters(),
            $parameters->getUnrecognizedParameters()
        );
    }

}
