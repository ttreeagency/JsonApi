<?php

namespace Ttree\JsonApi\Exception;

use Ttree\JsonApi\Exception;
use Neos\Flow\Annotations as Flow;

/**
 * Exception
 *
 * @Flow\Scope("singleton")
 * @api
 */
class ForbiddenException extends Exception
{
    /**
     * @var integer
     */
    protected $statusCode = 403;
}