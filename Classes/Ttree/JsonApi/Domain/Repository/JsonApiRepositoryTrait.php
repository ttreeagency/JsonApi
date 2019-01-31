<?php
namespace Ttree\JsonApi\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\SortParameterInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Ttree\JsonApi\Domain\Model\PaginationParameters;
use Ttree\JsonApi\Domain\Model\ResourceSettingsDefinition;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;

/**
 * Paginate Options
 */
trait JsonApiRepositoryTrait
{
    /**
     * @param EncodingParametersInterface $parameters
     * @param ResourceSettingsDefinition $resourceSettingsDefinition
     * @return QueryResultInterface
     * @throws \Neos\Flow\Exception
     * @throws \Ttree\JsonApi\Exception\ConfigurationException
     */
    public function findByJsonApiParameters(EncodingParametersInterface $parameters, ResourceSettingsDefinition $resourceSettingsDefinition)
    {
        /** @var QueryInterface $query */
        $query = $this->createQuery();

        if ($parameters->isEmpty()) {
            return $query->execute();
        }

        $paginationParameters = new PaginationParameters($parameters->getPaginationParameters() ?: []);
        $query->setLimit($paginationParameters->getLimit());
        if ($paginationParameters->hasPagination()) {
            $query->setOffset($paginationParameters->getOffset());
        }

        if ($parameters->getSortParameters()) {
            $ordering = [];
            foreach ($parameters->getSortParameters() as $sortParameter) {
                // todo better handling when the attributies does not exist (JSON API Error)
                $field = $resourceSettingsDefinition->convertSortableAttributes($sortParameter->getField());
                /** @var SortParameterInterface $sortParameter */
                $ordering[$field] = $sortParameter->isAscending() ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING;
            }
            $query->setOrderings($ordering);
        }
        return $query->execute();
    }

    /**
     * @param EncodingParametersInterface $parameters
     * @param ResourceSettingsDefinition $resourceSettingsDefinition
     * @return QueryResultInterface
     * @throws \Neos\Flow\Exception
     * @throws \Ttree\JsonApi\Exception\ConfigurationException
     */
    public function countByJsonApiParameters(EncodingParametersInterface $parameters, ResourceSettingsDefinition $resourceSettingsDefinition)
    {
        /** @var QueryInterface $query */
        $query = $this->createQuery();

        if ($parameters->isEmpty()) {
            return $query->execute();
        }

        return $query->execute();
    }
}
