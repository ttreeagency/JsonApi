<?php

namespace Flowpack\JsonApi\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Exception\NoSuchActionException;
use Flowpack\JsonApi\Adapter\AbstractAdapter;
use Flowpack\JsonApi\Adapter\DefaultAdapter;
use Flowpack\JsonApi\Exception;
use Flowpack\JsonApi\Exception\ConfigurationException;
use Flowpack\JsonApi\Exception\RuntimeException;
use Flowpack\JsonApi\Mvc\Controller\EncodingParametersParser;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Schema\Link;
use Neomerx\JsonApi\Schema\BaseSchema;
use Flowpack\JsonApi\Domain\Model\PaginationParameters;
use Flowpack\JsonApi\Mvc\ValidatedRequest;
use Flowpack\JsonApi\View\JsonApiView;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\UnsupportedRequestTypeException;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\Mvc\ResponseInterface;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Utility\Arrays;

/**
 * Class JsonApiController
 * @package Flowpack\JsonApi\Controller
 * @Flow\Scope("singleton")
 */
class JsonApiController extends ActionController
{
    /**
     * @var string
     */
    protected $defaultViewObjectName = 'Flowpack\JsonApi\View\JsonApiView';

    /**
     * @var array
     */
    protected $supportedMediaTypes = array('application/vnd.api+json');

    /**
     * @var array
     */
    protected $endpoint;

    /**
     * @var array
     */
    protected $resourceConfiguration;

    /**
     * @var JsonApiView
     */
    protected $view;

    /**
     * @var AbstractAdapter
     */
    protected $adapter;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var object
     */
    protected $record;

    /**
     * @var FactoryInterface
     * @Flow\Inject(lazy=false)
     */
    protected $factory;

    /**
     * @var ValidatedRequest
     */
    protected $validatedRequest;

    /**
     * @var EncodingParametersParser
     */
    protected $encodedParameters;

    /**
     * Initialize Action
     */
    protected function initializeAction()
    {
        parent::initializeAction();
        $this->response->setHeader('Content-Type', 'application/vnd.api+json');
    }

    /**
     * Initializes the controller
     *
     * This method should be called by the concrete processRequest() method.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws UnsupportedRequestTypeException
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws RuntimeException
     * @throws ConfigurationException
     */
    protected function initializeController(RequestInterface $request, ResponseInterface $response)
    {
        parent::initializeController($request, $response);

        /** @var ActionRequest $request */
        if ($request->hasArgument('@endpoint' === false)) {
            throw new ConfigurationException('Endpoint should be set');
        }

        $this->endpoint = $request->getArgument('@endpoint');
        $availableResources = $this->endpoint['resources'];

        $resource = $request->getArgument('@resource');
        if (!\array_key_exists($resource, $availableResources)) {
            $this->throwStatus(404);
        }

        $this->resourceConfiguration = $availableResources[$resource];

        // Default deny all
        $allowedMethods = [];
        if (isset($this->resourceConfiguration['allowedMethods'])) {
            $allowedMethods = $this->resourceConfiguration['allowedMethods'];
        }

        if (isset($this->resourceConfiguration['disallowedMethods'])) {
            foreach ($this->resourceConfiguration['disallowedMethods'] as $method) {
                unset($allowedMethods[$method]);
            }
        }
        if (!\in_array($this->request->getHttpRequest()->getMethod(), $allowedMethods)) {
            $this->throwStatus(403);
        }

        $this->validatedRequest = new ValidatedRequest($request);
        $this->encodedParameters = new EncodingParametersParser($request->getArguments());
        $this->registerAdapter($this->resourceConfiguration, $resource);

        $urlPrefix = $this->getUrlPrefix($request);
        $this->encoder = $this->adapter->getEncoder($urlPrefix);
    }

    /**
     * Determines the action method and assures that the method exists.
     * @return string
     * @throws UnsupportedRequestTypeException
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     */
    protected function resolveActionMethodName()
    {
        if ($this->request->getHttpRequest()->getMethod() === 'OPTIONS') {
            return 'optionsAction';
        }

        if ($this->validatedRequest->isIndex()) {
            return 'listAction';
        } elseif ($this->validatedRequest->isCreateResource()) {
            return 'createAction';
        }

        $this->record = $this->adapter->find($this->request->getArgument('identifier'));
        if (!$this->record) {
            $this->throwStatus(404);
        }

        if ($this->validatedRequest->isReadResource()) {
            return 'showAction';
        } elseif ($this->validatedRequest->isUpdateResource()) {
            return 'updateAction';
        } elseif ($this->validatedRequest->isDeleteResource()) {
            return 'deleteAction';
        }

        $relationship = $this->validatedRequest->getRelationshipName();

        /** Relationships */
        if ($this->validatedRequest->isReadRelatedResource() || $this->validatedRequest->isReadRelationship()) {
//            $this->validatedRequest->readRelationship($record, $field, $request);
            return 'relatedAction';
        } else {
//            $this->validatedRequest->modifyRelationship($record, $field, $request);
            return 'updateRelationshipAction';
        }
    }

    /**
     * @param ViewInterface $view
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     * @throws \Flowpack\JsonApi\Exception\ConfigurationException
     */
    protected function initializeView(ViewInterface $view)
    {
        /** @var JsonApiView $view */
        parent::initializeView($view);
        $view->setResource($this->request->getArgument('@resource'));
        $view->setEncoder($this->encoder);
        $view->setParameters($this->encodedParameters);
    }

    /**
     * @throws \Neos\Flow\Exception
     * @return void
     */
    public function listAction()
    {
        $isSubUrl = false;
        $hasMeta = false;

        $data = $this->adapter->query($this->encodedParameters);
        $count = $this->adapter->count($this->encodedParameters);

        $parameters = new PaginationParameters($this->encodedParameters->getPagination() ?: []);
        $arguments = $this->request->getHttpRequest()->getArguments();

        if ($arguments !== []) {
            $query = \http_build_query($arguments);
            $self = new Link($isSubUrl, \sprintf('/%s?%s', $this->adapter->getResource(), $query), $hasMeta);
        } else {
            $self = new Link($isSubUrl, \sprintf('/%s', $this->adapter->getResource()), $hasMeta);
        }

        $links = [
            Link::SELF => $self
        ];

        if ($count > $parameters->getLimit()) {
            $prev = $parameters->prev();
            if ($prev !== null) {
                $query = \http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $prev));
                $links[Link::PREV] = new Link($isSubUrl, \sprintf('/%s?%s', $this->adapter->getResource(), $query), $hasMeta);
            }

            $next = $parameters->next($count);
            if ($next !== null) {
                $query = \http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $next));
                $links[Link::NEXT] = new Link($isSubUrl, \sprintf('/%s?%s', $this->adapter->getResource(), $query), $hasMeta);
            }

            $first = $parameters->first();
            if ($first !== null) {
                $query = \http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $first));
                $links[Link::FIRST] = new Link($isSubUrl, \sprintf('/%s?%s', $this->adapter->getResource(), $query), $hasMeta);
            }

            $last = $parameters->last($count);
            if ($last !== null) {
                $query = \http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $last));
                $links[Link::LAST] = new Link($isSubUrl, \sprintf('/%s?%s', $this->adapter->getResource(), $query), $hasMeta);
            }
        }

        $this->encoder->withLinks($links)->withMeta([
            'total' => $count
        ]);

        $this->view->setData($data);
    }

    /**
     * @param string $identifier
     * @return void
     */
    public function showAction($identifier)
    {
        $data = $this->adapter->read($identifier, $this->encodedParameters);

        $this->view->setData($data);
    }

    /**
     * @param string $resource
     * @param string $relationship
     * @throws RuntimeException
     * @throws UnsupportedRequestTypeException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     */
    public function relatedAction(string $resource, string $relationship)
    {
        /** @var BaseSchema $schema */
        $schema = $this->getSchema($resource);
        $relationships = $schema->getRelationships($this->record);
        if (!isset($relationships[$relationship])) {
            $this->throwStatus(404, \sprintf('Relationship "%s" not found', $relationship));
        }
        $this->view->setData($relationships[$relationship][BaseSchema::RELATIONSHIP_DATA]);
    }

    /**
     * @throws RuntimeException
     * @throws \Neos\Flow\Http\Exception
     */
    public function createAction()
    {
        try {
            $data = $this->adapter->create($this->validatedRequest->getDocument()->getResource(), $this->encodedParameters);
        } catch (Exception\InvalidJsonException $e) {
            $this->response->setStatus(406);
            return;
        }

        $this->response->setStatus(201);
        $this->view->setData($data);
    }

    /**
     * @throws RuntimeException
     * @throws \Neos\Flow\Http\Exception
     */
    public function updateAction()
    {
        try {
            $data = $this->adapter->update($this->record, $this->validatedRequest->getDocument()->getResource(), $this->encodedParameters);
        } catch (Exception\InvalidJsonException $e) {
            $this->response->setStatus(406);
            return;
        }

        $this->persistenceManager->persistAll();
        $this->response->setStatus(200);
        $this->view->setData($data);
    }

    /**
     * @return string
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteAction()
    {
        $this->adapter->delete($this->record, $this->encodedParameters);

        $this->response->setStatus(204);
        return '';
    }

    /**
     * Returns the supported request methods for a single and set the "Allow" header accordingly
     *
     * @return string
     * @throws UnsupportedRequestTypeException
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     */
    public function optionsAction()
    {
        $allowedMethods = array(
            'GET',
            'POST',
            'PATCH',
            'DELETE'
        );

        $this->response->setHeader('Access-Control-Allow-Methods', \implode(', ', \array_unique($allowedMethods)));
        $this->response->setStatus(204);
        return '';
    }

    /**
     * @param array $configuration
     * @param string $resource
     * @return void
     * @throws RuntimeException
     */
    protected function registerAdapter($configuration, $resource): void
    {
        if (isset($configuration['adapter'])) {
            $adapterClass = $configuration['adapter'];
            if ($this->objectManager->isRegistered($adapterClass)) {
                $this->adapter = new $adapterClass($configuration, $resource, $this->encodedParameters);
                return;
            }

            throw new RuntimeException(\sprintf('Adapter %s is not registered', $adapterClass));
        }

        $this->adapter = new DefaultAdapter($configuration, $resource, $this->encodedParameters);
    }

    /**
     * @param string $resource
     * @param string $relation
     * @return BaseSchema
     * @throws RuntimeException
     */
    protected function getSchema(string $resource, string $relation = ''): BaseSchema
    {
        if (isset($this->resourceConfiguration['related'])) {
            if ($relation !== '') {
                if (isset($this->resourceConfiguration['related'][$relation])) {
                    $schemaClass = \key($this->resourceConfiguration['related'][$relation]);
                    if ($this->objectManager->isRegistered($schemaClass)) {
                        return new $schemaClass();
                    }

                    throw new RuntimeException(\sprintf('Schema %s is not registered', $schemaClass));
                }

                throw new RuntimeException(\sprintf('Missing related definition for %s in `endpoints.resources.%s.related.%s` not registered!', $resource, $resource, $relation));
            }

            $schemaClass = $this->resourceConfiguration['schema'];
            if ($this->objectManager->isRegistered($schemaClass)) {
                return new $schemaClass();
            }

            throw new RuntimeException(\sprintf('Schema %s is not registered', $schemaClass));
        }

        throw new RuntimeException(\sprintf('Missing related definition for %s in `endpoints.resources.%s.related` not registered!', $resource, $resource));
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    protected function getUrlPrefix(RequestInterface $request)
    {
        $suffix = isset($this->endpoint['baseUrl']) && isset($this->endpoint['version']) ? $this->endpoint['baseUrl'] . '/' . $this->endpoint['version'] : '/';
        return \rtrim($request->getMainRequest()->getHttpRequest()->getBaseUri() . $suffix, '/');
    }

}
