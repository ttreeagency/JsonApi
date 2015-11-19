<?php
namespace Ttree\JsonApi\Domain\Model;

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
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\View\AbstractView;

/**
 * Paginate Options
 */
class PaginateOptions
{
    /**
     * @var integer
     */
    protected $offset;

    /**
     * @var integer
     */
    protected $limit;

    /**
     * PaginateOptions constructor.
     * @param int $offset
     * @param int $limit
     */
    public function __construct($offset, $limit)
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
}
