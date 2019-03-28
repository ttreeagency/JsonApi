<?php

namespace Flowpack\JsonApi\Mvc;

use Flowpack\JsonApi\Object\Document;
use Flowpack\JsonApi\Object\ResourceIdentifier;
use Flowpack\JsonApi\Exception\InvalidJsonException;
use Flowpack\JsonApi\Mvc\Controller\EncodingParametersParser;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neos\Flow\Http\Request as HttpRequest;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\RequestInterface;

/**
 * Class ValidatedRequest
 *
 * @package Flowpack\JsonApi
 */
class ValidatedRequest
{
    /**
     * @var RequestInterface
     */
    protected $serverRequest;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var string|null
     */
    protected $resourceId;

    /**
     * @var object|bool|null
     */
    protected $document;

    /**
     * @var EncodingParametersParser|null
     */
    protected $parameters;

    /**
     * ValidatedRequest constructor.
     *
     * @param RequestInterface $serverRequest
     */
    public function __construct(RequestInterface $serverRequest)
    {

        if (!$serverRequest instanceof HttpRequest && !$serverRequest instanceof ActionRequest) {
            throw new \InvalidArgumentException('The parent request passed to ActionRequest::__construct() must be either an HTTP request or another ActionRequest', 1327846149);
        }
        $this->serverRequest = $serverRequest;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return $this->serverRequest->getArgument('resource');
    }

    /**
     * @inheritdoc
     */
    public function getResourceId()
    {
        /** Cache the resource id because binding substitutions will override it. */
        if (\is_null($this->resourceId)) {
            $this->resourceId = $this->serverRequest->getArgument('identifier') ?: false;
        }

        return $this->resourceId ?: null;
    }

    /**
     * @inheritdoc
     */
    public function getResourceIdentifier()
    {
        if (!$resourceId = $this->getResourceId()) {
            return null;
        }

        return ResourceIdentifier::create($this->getResourceType(), $resourceId);
    }

    /**
     * @inheritdoc
     */
    public function getResource()
    {
        $resource = $this->serverRequest->getArgument('resource');

        return \is_object($resource) ? $resource : null;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipName()
    {
        return $this->serverRequest->getArgument('relationship');
    }

    /**
     * @todo
     * @inheritdoc
     */
    public function getInverseResourceType()
    {
//        return $this->serverRequest->route(ResourceRegistrar::PARAM_RELATIONSHIP_INVERSE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function getParameters()
    {
        if ($this->parameters) {
            return $this->parameters;
        }

        return $this->parameters = $this->parseParameters();
    }

    /**
     * @return bool|null|object|Document
     * @throws \Neos\Flow\Http\Exception
     */
    public function getDocument()
    {
        if (\is_null($this->document)) {
            $this->document = new Document($this->decodeDocument());
        }

        return $this->document ?? null;
    }

    /**
     * @inheritdoc
     */
    public function isIndex(): bool
    {
        return $this->isMethod('get') && !$this->isResource();
    }

    /**
     * @inheritdoc
     */
    public function isCreateResource(): bool
    {
        return $this->isMethod('post') && !$this->isResource();
    }

    /**
     * @inheritdoc
     */
    public function isReadResource(): bool
    {
        return $this->isMethod('get') && $this->isResource() && !$this->isRelationship();
    }

    /**
     * @inheritdoc
     */
    public function isUpdateResource(): bool
    {
        return $this->isMethod('patch') && $this->isResource() && !$this->isRelationship();
    }

    /**
     * @inheritdoc
     */
    public function isDeleteResource(): bool
    {
        return $this->isMethod('delete') && $this->isResource() && !$this->isRelationship();
    }

    /**
     * @inheritdoc
     */
    public function isReadRelatedResource(): bool
    {
        return $this->isRelationship() && !$this->hasRelationships();
    }

    /**
     * @inheritdoc
     */
    public function hasRelationships(): bool
    {
        return $this->serverRequest->hasArgument('relationship');
    }

    /**
     * @inheritdoc
     */
    public function isReadRelationship(): bool
    {
        return $this->isMethod('get') && $this->hasRelationships();
    }

    /**
     * @inheritdoc
     */
    public function isModifyRelationship(): bool
    {
        return $this->isReplaceRelationship() ||
            $this->isAddToRelationship() ||
            $this->isRemoveFromRelationship();
    }

    /**
     * @inheritdoc
     */
    public function isReplaceRelationship(): bool
    {
        return $this->isMethod('patch') && $this->hasRelationships();
    }

    /**
     * @inheritdoc
     */
    public function isAddToRelationship(): bool
    {
        return $this->isMethod('post') && $this->hasRelationships();
    }

    /**
     * @inheritdoc
     */
    public function isRemoveFromRelationship(): bool
    {
        return $this->isMethod('delete') && $this->hasRelationships();
    }

    /**
     * @return bool
     */
    protected function isResource(): bool
    {
        return !empty($this->getResourceId());
    }

    /**
     * Is the HTTP request method the one provided?
     *
     * @param string $method the expected method - case insensitive.
     * @return bool
     */
    protected function isMethod($method): bool
    {
        return \strtoupper($this->serverRequest->getHttpRequest()->getMethod()) === \strtoupper($method);
    }

    /**
     * @return bool
     */
    protected function isRelationship(): bool
    {
        return $this->serverRequest->hasArgument('relationship');
    }

    /**
     * Extract the JSON API document from the request.
     *
     * @param bool $assoc
     * @return mixed|null
     * @throws InvalidJsonException
     * @throws \Neos\Flow\Http\Exception
     */
    protected function decodeDocument($assoc = false)
    {
        $decoded = \json_decode((string)$this->serverRequest->getHttpRequest()->getBody(), $assoc, 512, 0);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw InvalidJsonException::create();
        }

        if (!$assoc && !is_object($decoded)) {
            throw new InvalidJsonException(null, 'JSON is not an object.');
        }

        if ($assoc && !is_array($decoded)) {
            throw new InvalidJsonException(null, 'JSON is not an object or array.');
        }

        return $decoded;
    }
}
