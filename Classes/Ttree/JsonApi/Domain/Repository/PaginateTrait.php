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
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Ttree\JsonApi\Domain\Model\PaginateOptions;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\View\AbstractView;
use TYPO3\Flow\Persistence\QueryResultInterface;

/**
 * Paginate Options
 */
trait PaginateTrait
{
    /**
     * @param PaginateOptions $options
     * @return QueryResultInterface
     */
    public function paginate(PaginateOptions $options) {
        $query = $this->createQuery()
            ->setOffset($options->getOffset())
            ->setLimit($options->getLimit());

        return $query->execute();
    }
}
