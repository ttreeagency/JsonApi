<?php
namespace Flowpack\JsonApi\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Flowpack\JsonApi\Mvc\Controller\EncodingParametersParser;
use Flowpack\JsonApi\Domain\Model\PaginationParameters;
use Flowpack\JsonApi\Domain\Model\ResourceSettingsDefinition;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;

/**
 * Paginate Options
 */
trait JsonApiRepositoryTrait
{
    /**
     * @param EncodingParametersParser $parameters
     * @param ResourceSettingsDefinition $resourceSettingsDefinition
     * @return QueryResultInterface
     * @throws \Neos\Flow\Exception
     * @throws \Flowpack\JsonApi\Exception\ConfigurationException
     */
    public function findByJsonApiParameters(EncodingParametersParser $parameters, ResourceSettingsDefinition $resourceSettingsDefinition)
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
            foreach ($parameters->getSorts() as $sortParameter => $value) {
                // @todo better handling when the attributies does not exist (JSON API Error)
                $field = $resourceSettingsDefinition->convertSortableAttributes($sortParameter);
                $ordering[$field] = $value ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING;
            }
            $query->setOrderings($ordering);
        }
        return $query->execute();
    }

    /**
     * @param EncodingParametersParser $parameters
     * @param ResourceSettingsDefinition $resourceSettingsDefinition
     * @return QueryResultInterface
     * @throws \Neos\Flow\Exception
     * @throws \Flowpack\JsonApi\Exception\ConfigurationException
     */
    public function countByJsonApiParameters(EncodingParametersParser $parameters, ResourceSettingsDefinition $resourceSettingsDefinition)
    {
        /** @var QueryInterface $query */
        $query = $this->createQuery();

        if ($parameters->isEmpty()) {
            return $query->execute();
        }

        return $query->execute();
    }
}
