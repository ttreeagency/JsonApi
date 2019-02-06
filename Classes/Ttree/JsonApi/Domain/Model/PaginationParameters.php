<?php

namespace Ttree\JsonApi\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;

/**
 * Paginate Options
 */
class PaginationParameters
{
    const STRATEGY_NONE = 0;
    const STRATEGY_NUMBER = 1;
    const STRATEGY_OFFSET = 2;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="pagination")
     */
    protected $settings;

    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var integer
     */
    protected $offset;

    /**
     * @var integer
     */
    protected $strategy;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param array $parameters
     * @throws Exception
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @throws Exception
     */
    public function initializeObject()
    {
        $parameters = $this->parameters;
        if (isset($parameters['number']) || $parameters === []) {
            $number = isset($parameters['number']) ? (integer)$parameters['number'] : 0;
            $this->limit = isset($parameters['size']) ? (integer)$parameters['size'] : $this->settings['defaultPageSize'];
            $this->offset = ($number > 0 ? $number - 1 : 0) * $this->limit;
            $this->strategy = self::STRATEGY_NUMBER;
        } elseif (isset($parameters['offset'])) {
            $offset = (integer)$parameters['offset'];
            $this->limit = isset($parameters['limit']) ? (integer)$parameters['limit'] : $this->settings['defaultPageSize'];
            $this->offset = $offset * $this->limit;
            $this->strategy = self::STRATEGY_OFFSET;
        } else {
            throw new Exception('Invalid pagination parameters', 1449348020);
        }

        if ($this->limit > $this->settings['maximumPageSize']) {
            throw new Exception(\sprintf('Maximum page (%s) size exceeded', $this->settings['maximumPageSize']), 1449347468);
        }
    }

    /**
     * @return boolean
     */
    public function hasPagination()
    {
        return $this->strategy > self::STRATEGY_NONE;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function first()
    {
        switch ($this->strategy) {
            case self::STRATEGY_NUMBER:
                return [
                    'page' => [
                        'number' => 1,
                        'size' => $this->limit,
                    ]
                ];
            case self::STRATEGY_OFFSET:
                return [
                    'page' => [
                        'offset' => 0,
                        'limit' => $this->limit,
                    ]
                ];
            default:
                throw new Exception('Invalid pagination strategy', 1449348395);
        }
    }

    /**
     * @param integer $count
     * @return array
     * @throws Exception
     */
    public function last($count)
    {
        switch ($this->strategy) {
            case self::STRATEGY_NUMBER:
                return [
                    'page' => [
                        'number' => $this->limit > 0 ? \ceil($count / $this->limit) : 0,
                        'size' => $this->limit,
                    ]
                ];
            case self::STRATEGY_OFFSET:
                return [
                    'page' => [
                        'offset' => \floor($count / $this->limit),
                        'limit' => $this->limit,
                    ]
                ];
            default:
                throw new Exception('Invalid pagination strategy', 1449348396);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function prev()
    {
        if ($this->offset === 0) {
            return null;
        }
        switch ($this->strategy) {
            case self::STRATEGY_NUMBER:
                return [
                    'page' => [
                        'number' => ($this->offset / $this->limit),
                        'size' => $this->limit,
                    ]
                ];
            case self::STRATEGY_OFFSET:
                return [
                    'page' => [
                        'offset' => ($this->offset / $this->limit) - 1,
                        'limit' => $this->limit,
                    ]
                ];
            default:
                throw new Exception('Invalid pagination strategy', 1449348396);
        }
    }

    /**
     * @param integer $count
     * @return array
     * @throws Exception
     */
    public function next($count)
    {
        if ($this->limit === null) {
            return null;
        }

        if (($this->offset / $this->limit) + 1 >= \ceil($count / $this->limit)) {
            return null;
        }
        switch ($this->strategy) {
            case self::STRATEGY_NUMBER:
                return [
                    'page' => [
                        'number' => ($this->offset / $this->limit) + 2,
                        'size' => $this->limit,
                    ]
                ];
            case self::STRATEGY_OFFSET:
                return [
                    'page' => [
                        'offset' => ($this->offset / $this->limit) + 1,
                        'limit' => $this->limit,
                    ]
                ];
            default:
                throw new Exception('Invalid pagination strategy', 1449348396);
        }
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }
}
