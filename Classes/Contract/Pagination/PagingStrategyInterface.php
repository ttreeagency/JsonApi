<?php

namespace Ttree\JsonApi\Contract\Pagination;

use Ttree\JsonApi\Mvc\Controller\EncodingParametersParser;

/**
 * Interface PagingStrategyInterface
 *
 * @package Ttree\JsonApi
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
