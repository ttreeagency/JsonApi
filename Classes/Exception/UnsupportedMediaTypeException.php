<?php

namespace Flowpack\JsonApi\Exception;

use Flowpack\JsonApi\Exception;
use Neos\Flow\Annotations as Flow;

/**
 * Exception
 *
 * @Flow\Scope("singleton")
 * @api
 */
class UnsupportedMediaTypeException extends Exception
{
    /**
     * @var integer
     */
    protected $statusCode = 415;
}
