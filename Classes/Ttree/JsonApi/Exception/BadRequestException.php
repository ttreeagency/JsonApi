<?php
namespace Ttree\JsonApi\Exception;

use Neos\Flow\Annotations as Flow;
use Ttree\JsonApi\Exception;

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
