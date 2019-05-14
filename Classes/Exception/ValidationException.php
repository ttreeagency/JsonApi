<?php

namespace Flowpack\JsonApi\Exception;

use Exception;
use Neomerx\JsonApi\Schema\ErrorCollection;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * Class ValidationException
 *
 * @package CloudCreativity\LaravelJsonApi
 */
class ValidationException extends JsonApiException
{

    /**
     * ValidationException constructor.
     *
     * @param ErrorInterface|ErrorInterface[]|ErrorCollection $errors
     * @param string|int|null $defaultHttpCode
     * @param Exception|null $previous
     */
    public function __construct($errors, $defaultHttpCode = self::DEFAULT_HTTP_CODE, Exception $previous = null)
    {
        parent::__construct($errors, $defaultHttpCode, $previous);
    }
}
