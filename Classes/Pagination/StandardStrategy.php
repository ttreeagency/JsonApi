<?php

namespace Ttree\JsonApi\Pagination;

//use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
//use Neomerx\JsonApi\Contracts\Http\Query\QueryParametersParserInterface;
use Ttree\JsonApi\Contract\Pagination\PagingStrategyInterface;
use Ttree\JsonApi\Mvc\Controller\EncodingParametersParser;

/**
 * Class StandardStrategy
 *
 * @package Ttree\JsonApi
 */
class StandardStrategy implements PagingStrategyInterface
{

//    use CreatesPages;

    /**
     * @var string|null
     */
    protected $pageKey;

    /**
     * @var string|null
     */
    protected $perPageKey;

    /**
     * @var array|null
     */
    protected $columns;

    /**
     * @var bool|null
     */
    protected $simplePagination;

    /**
     * @var bool|null
     */
    protected $underscoreMeta;

    /**
     * @var string|null
     */
    protected $metaKey;

    /**
     * StandardStrategy constructor.
     */
    public function __construct()
    {
//        $this->metaKey = QueryParametersParserInterface::PARAM_PAGE;
    }

    /**
     * @param $key
     * @return $this
     */
    public function withPageKey($key)
    {
        $this->pageKey = $key;

        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function withPerPageKey($key)
    {
        $this->perPageKey = $key;

        return $this;
    }

    /**
     * @param $cols
     * @return $this;
     */
    public function withColumns($cols)
    {
        $this->columns = $cols;

        return $this;
    }

    /**
     * @return $this
     */
    public function withSimplePagination()
    {
        $this->simplePagination = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function withUnderscoredMetaKeys()
    {
        $this->underscoreMeta = true;

        return $this;
    }

    /**
     * Set the key for the paging meta.
     *
     * Use this to 'nest' the paging meta in a sub-key of the JSON API document's top-level meta object.
     * A string sets the key to use for nesting. Use `null` to indicate no nesting.
     *
     * @param string|null $key
     * @return $this
     */
    public function withMetaKey($key)
    {
        $this->metaKey = $key ?: null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function paginate($query, EncodingParametersParser $parameters)
    {
        $pageParameters = new Collection((array) $parameters->getPaginationParameters());
        $paginator = $this->query($query, $pageParameters);

        return $this->createPage($paginator, $parameters);
    }

    /**
     * @param Collection $collection
     * @return int
     */
    protected function getPerPage(Collection $collection)
    {
        return (int) $collection->get($this->getPerPageKey());
    }

    /**
     * Get the default per-page value for the query.
     *
     * If the query is an Doctrine builder, we can pass in `null` as the default,
     * which then delegates to the model to get the default. Otherwise the Laravel
     * standard default is 15.
     *
     * @param $query
     * @return int|null
     */
    protected function getDefaultPerPage($query)
    {
//        return $query instanceof DoctrineBuilder ? null : 15;
    }

    /**
     * @return array
     */
    protected function getColumns()
    {
        return $this->columns ?: ['*'];
    }

    /**
     * @return bool
     */
    protected function isSimplePagination()
    {
        return (bool) $this->simplePagination;
    }

    /**
     * @param mixed $query
     * @param Collection $pagingParameters
     * @return mixed
     */
    protected function query($query, Collection $pagingParameters)
    {
        $pageName = $this->getPageKey();
        $size = $this->getPerPage($pagingParameters) ?: $this->getDefaultPerPage($query);
        $cols = $this->getColumns();

        return ($this->isSimplePagination() && method_exists($query, 'simplePaginate')) ?
            $query->simplePaginate($size, $cols, $pageName) :
            $query->paginate($size, $cols, $pageName);
    }

}
