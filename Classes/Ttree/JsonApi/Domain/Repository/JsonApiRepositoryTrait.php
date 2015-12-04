<?php
namespace Ttree\JsonApi\Domain\Repository;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use Neomerx\JsonApi\Contracts\Parameters\SortParameterInterface;
use Ttree\JsonApi\Domain\Model\PaginateOptions;
use Ttree\JsonApi\Domain\Model\ResourceSettingsDefinition;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Flow\Persistence\QueryResultInterface;

/**
 * Paginate Options
 */
trait JsonApiRepositoryTrait
{
    /**
     * @param ParametersInterface $parameters
     * @param ResourceSettingsDefinition $resourceSettingsDefinition
     * @return QueryResultInterface
     */
    public function findByJsonApiParameters(ParametersInterface $parameters, ResourceSettingsDefinition $resourceSettingsDefinition)
    {
        /** @var QueryInterface $query */
        $query = $this->createQuery();

        $query->setLimit(50);
        if ($parameters->isEmpty()) {
            return $query->execute();
        }

        if ($parameters->getPaginationParameters()) {

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
}
