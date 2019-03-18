<?php

namespace Flowpack\JsonApi\Exception;

use Neos\Flow\Annotations as Flow;
use Flowpack\JsonApi\Exception;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Flowpack\JsonApi\Document\Error;

/**
 * Exception
 *
 * @Flow\Scope("singleton")
 * @api
 */
class InvalidJsonException extends JsonApiException
{
    /**
     * @var int
     */
    private $jsonError;

    /**
     * @var string
     */
    private $jsonErrorMessage;

    /**
     * @param int $defaultHttpCode
     * @param Exception|null $previous
     * @return InvalidJsonException
     */
    public static function create($defaultHttpCode = self::HTTP_CODE_BAD_REQUEST, Exception $previous = null)
    {
        return new self(\json_last_error(), \json_last_error_msg(), $defaultHttpCode, $previous);
    }

    /**
     * InvalidJsonException constructor.
     *
     * @param int|null $jsonError
     * @param string|null $jsonErrorMessage
     * @param int $defaultHttpCode
     * @param Exception|null $previous
     */
    public function __construct(
        $jsonError = null,
        $jsonErrorMessage = null,
        $defaultHttpCode = self::HTTP_CODE_BAD_REQUEST,
        Exception $previous = null
    )
    {
        parent::__construct([], $defaultHttpCode, $previous);

        $this->jsonError = $jsonError;
        $this->jsonErrorMessage = $jsonErrorMessage;

        $this->addError(Error::create([
            Error::TITLE => 'Invalid JSON',
            Error::STATUS => self::HTTP_CODE_BAD_REQUEST,
            Error::CODE => $jsonError,
            Error::DETAIL => $jsonErrorMessage,
        ]));
    }

    /**
     * @return int|null
     */
    public function getJsonError()
    {
        return $this->jsonError;
    }

    /**
     * @return string|null
     */
    public function getJsonErrorMessage()
    {
        return $this->jsonErrorMessage;
    }
}
