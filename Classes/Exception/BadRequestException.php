<?php

namespace Flowpack\JsonApi\Exception;

use Neos\Flow\Annotations as Flow;
use Flowpack\JsonApi\Exception;

/**
 * Exception
 *
 * @Flow\Scope("singleton")
 * @api
 */
class BadRequestException extends Exception
{
    /**
     * @var integer
     */
    protected $statusCode = 400;
}
