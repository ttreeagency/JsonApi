<?php
namespace Ttree\JsonApi\Integration;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;
use Ttree\JsonApi\Exception;
use TYPO3\Flow\Annotations as Flow;

/**
 * Exception
 *
 * @Flow\Scope("singleton")
 * @api
 */
class ExceptionThrower implements ExceptionThrowerInterface
{
    /**
     * Throw 'Bad request' exception (HTTP code 400).
     *
     * @throws Exception\BadRequestException
     */
    public function throwBadRequest()
    {
        throw new Exception\BadRequestException('Bad request', 1449130902);
    }

    /**
     * Throw 'Forbidden' exception (HTTP code 403).
     *
     * @throws Exception\ForbiddenException
     */
    public function throwForbidden()
    {
        throw new Exception\ForbiddenException('Forbidden', 1449130939);
    }

    /**
     * Throw 'Not Acceptable' exception (HTTP code 406).
     *
     * @throws Exception\NotAcceptableException
     */
    public function throwNotAcceptable()
    {
        throw new Exception\NotAcceptableException('Not Acceptable', 1449131015);
    }

    /**
     * Throw 'Conflict' exception (HTTP code 409).
     *
     * @throws Exception\ConflictException
     */
    public function throwConflict()
    {
        throw new Exception\ConflictException('Conflict', 1449131059);
    }

    /**
     * Throw 'Unsupported Media Type' exception (HTTP code 415).
     *
     * @return void
     */
    public function throwUnsupportedMediaType()
    {
        throw new Exception\UnsupportedMediaTypeException('Unsupported Media Type', 1449131090);
    }
}
