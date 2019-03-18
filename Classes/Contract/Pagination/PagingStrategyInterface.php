<?php

namespace Flowpack\JsonApi\Contract\Pagination;

use Flowpack\JsonApi\Mvc\Controller\EncodingParametersParser;

/**
 * Interface PagingStrategyInterface
 *
 * @package Flowpack\JsonApi
 */
interface PagingStrategyInterface
{

    /**
     * @param mixed $query
     * @param EncodingParametersParser $pagingParameters
     * @return PageInterface
     */
    public function paginate($query, EncodingParametersParser $pagingParameters);

}
