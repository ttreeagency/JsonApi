<?php

namespace Flowpack\JsonApi\Mvc\Controller;

use Neomerx\JsonApi\Contracts\Schema\ErrorInterface;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface as P;
use Neomerx\JsonApi\Schema\Error;
use Flowpack\JsonApi\Contract\Parameters\EncodingParametersInterface;


/**
 * Class EncodingParametersParser
 * @package Flowpack\JsonApi\Mvc\Controller
 */
class EncodingParametersParser implements EncodingParametersInterface
{
    /**
     * @var null|array
     */
    protected $fields = null;

    /**
     * @var null|array
     */
    protected $sorts = null;

    /**
     * @var null|array
     */
    protected $includes = null;

    /**
     * @var null
     */
    protected $filters = null;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string[]|null
     */
    protected $messages;

    /**
     * @param array $parameters
     * @param string[]|null $messages
     */
    public function __construct(array $parameters = [], array $messages = null)
    {
        $this->setParameters($parameters)->setMessages($messages);
    }

    /**
     * @return iterable
     */
    public function getFilters(): iterable
    {
        if (\array_key_exists(P::PARAM_FILTER, $this->parameters) === true) {
            $fields = $this->parameters[P::PARAM_FILTER];
            foreach ($fields as $type => $fieldList) {
                yield $type => $this->splitCommaSeparatedStringAndCheckNoEmpties(
                    $type,
                    $fieldList,
                    self::MSG_ERR_INVALID_PARAMETER
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getFields(): iterable
    {
        if (\array_key_exists(P::PARAM_FIELDS, $this->parameters) === true) {
            $fields = $this->parameters[P::PARAM_FIELDS];
            if (\is_array($fields) === false || empty($fields) === true) {
                throw new JsonApiException($this->createParameterError(P::PARAM_FIELDS, self::MSG_ERR_INVALID_PARAMETER));
            }

            foreach ($fields as $type => $fieldList) {
                yield $type => $this->splitCommaSeparatedStringAndCheckNoEmpties($type, $fieldList, self::MSG_ERR_INVALID_PARAMETER);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getIncludes(): iterable
    {
        if (\array_key_exists(P::PARAM_INCLUDE, $this->parameters) === true) {
            $paramName = P::PARAM_INCLUDE;
            $includes = $this->parameters[P::PARAM_INCLUDE];
            foreach ($this->splitCommaSeparatedStringAndCheckNoEmpties($paramName, $includes, self::MSG_ERR_INVALID_PARAMETER) as $path) {
                yield $path => $this->splitStringAndCheckNoEmpties(P::PARAM_INCLUDE, $path, '.', self::MSG_ERR_INVALID_PARAMETER);
            }
        }
    }

    /**
     * @return iterable
     */
    public function getSorts(): iterable
    {
        if (\array_key_exists(P::PARAM_SORT, $this->parameters) === true) {
            $sorts = $this->parameters[P::PARAM_SORT];

            $values = $this->splitCommaSeparatedStringAndCheckNoEmpties(P::PARAM_SORT, $sorts, self::MSG_ERR_INVALID_PARAMETER);
            foreach ($values as $orderAndField) {
                switch ($orderAndField[0]) {
                    case '-':
                        $isAsc = false;
                        $field = \substr($orderAndField, 1);
                        break;
                    case '+':
                        $isAsc = true;
                        $field = \substr($orderAndField, 1);
                        break;
                    default:
                        $isAsc = true;
                        $field = $orderAndField;
                        break;
                }

                yield $field => $isAsc;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getProfileUrls(): iterable
    {
        if (\array_key_exists(P::PARAM_PROFILE, $this->parameters) === true) {
            $encodedUrls = $this->parameters[P::PARAM_PROFILE];
            $decodedUrls = \urldecode($encodedUrls);
            yield from $this->splitSpaceSeparatedStringAndCheckNoEmpties(
                P::PARAM_PROFILE,
                $decodedUrls,
                self::MSG_ERR_INVALID_PARAMETER
            );
        }
    }

    /**
     * @return array
     */
    public function getPagination(): array
    {
        if (\array_key_exists(P::PARAM_PAGE, $this->parameters) === true) {
            $encodedUrls = $this->parameters[P::PARAM_PAGE];
            $decodedUrls = \urldecode($encodedUrls);
            return $this->splitSpaceSeparatedStringAndCheckNoEmpties(
                P::PARAM_PAGE,
                $decodedUrls,
                self::MSG_ERR_INVALID_PARAMETER
            );
        }

        return [];
    }

    /**
     * @param array $parameters
     *
     * @return self
     */
    protected function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return array
     */
    protected function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $messages
     *
     * @return self
     */
    protected function setMessages(?array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @param string $message
     *
     * @return string
     */
    protected function getMessage(string $message): string
    {
        $hasTranslation = $this->messages !== null && \array_key_exists($message, $this->messages) === false;

        return $hasTranslation === true ? $this->messages[$message] : $message;
    }

    /**
     * @param iterable $iterable
     *
     * @return array
     */
    protected function iterableToArray(iterable $iterable): array
    {
        $result = [];
        foreach ($iterable as $key => $value) {
            $result[$key] = $value instanceof \Generator ? $this->iterableToArray($value) : $value;
        }
        return $result;
    }

    /**
     * @param string $paramName
     * @param string|mixed $shouldBeString
     * @param string $errorTitle
     *
     * @return iterable
     */
    protected function splitCommaSeparatedStringAndCheckNoEmpties(
        string $paramName,
        $shouldBeString,
        string $errorTitle
    ): iterable
    {
        return $this->splitStringAndCheckNoEmpties($paramName, $shouldBeString, ',', $errorTitle);
    }

    /**
     * @param string $paramName
     * @param string|mixed $shouldBeString
     * @param string $errorTitle
     *
     * @return iterable
     */
    protected function splitSpaceSeparatedStringAndCheckNoEmpties(
        string $paramName,
        $shouldBeString,
        string $errorTitle
    ): iterable
    {
        return $this->splitStringAndCheckNoEmpties($paramName, $shouldBeString, ' ', $errorTitle);
    }

    /**
     * @param string $paramName
     * @param string|mixed $shouldBeString
     * @param string $separator
     * @param string $errorTitle
     *
     * @return iterable
     */
    protected function splitStringAndCheckNoEmpties(
        string $paramName,
        $shouldBeString,
        string $separator,
        string $errorTitle
    ): iterable
    {
        if (is_string($shouldBeString) === false || ($trimmed = \trim($shouldBeString)) === '') {
            throw new JsonApiException($this->createParameterError($paramName, $errorTitle));
        }

        foreach (\explode($separator, $trimmed) as $value) {
            $trimmedValue = \trim($value);
            if ($trimmedValue === '') {
                throw new JsonApiException($this->createParameterError($paramName, $errorTitle));
            }

            yield $trimmedValue;
        }
    }

    /**
     * @param string $paramName
     * @param string $errorTitle
     *
     * @return ErrorInterface
     */
    private function createParameterError(string $paramName, string $errorTitle): ErrorInterface
    {
        $source = [Error::SOURCE_PARAMETER => $paramName];
        $error = new Error(null, null, null, null, null, $errorTitle, null, $source);

        return $error;
    }
}