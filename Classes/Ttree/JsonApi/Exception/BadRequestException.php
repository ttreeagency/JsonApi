<?php
namespace Ttree\JsonApi\Exception;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Ttree\JsonApi\Exception;
use TYPO3\Flow\Annotations as Flow;

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
