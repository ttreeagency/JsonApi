<?php
namespace Ttree\JsonApi\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Exception\NoSuchActionException;
use Ttree\JsonApi\Adapter\AbstractAdapter;
use Ttree\JsonApi\Adapter\DefaultAdapter;
use Ttree\JsonApi\Exception\ConfigurationException;
use Ttree\JsonApi\Exception\RuntimeException;
use Ttree\JsonApi\Mvc\Controller\QueryParametersParser;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Schema\SchemaProvider;
use Ttree\JsonApi\Domain\Model\PaginationParameters;
use Ttree\JsonApi\Mvc\ValidatedRequest;
use Ttree\JsonApi\View\JsonApiView;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\UnsupportedRequestTypeException;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\Mvc\ResponseInterface;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Utility\Arrays;

/**
 * Class JsonApiController
 * @package Ttree\JsonApi\Controller
 * @Flow\Scope("singleton")
 */
class JsonApiController extends ActionController
{
    /**
     * @var string
     */
    protected $defaultViewObjectName = 'Ttree\JsonApi\View\JsonApiView';

    /**
     * @var array
     */
    protected $supportedMediaTypes = array('application/vnd.api+json');

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
     * @var EncodingParametersInterface
     */
    protected $parameters;

    /**
     * @Flow\InjectConfiguration(path="endpoints")
     * @var array
     */
    protected $availableEndpoints;

    /**
     * @var array
     */
    protected $availableResources;

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
        if ($request->hasArgument('endpoint' === false)) {
            throw new ConfigurationException('Endpoint should be set');
        }
        $endpoint = $request->getArgument('endpoint');
        $this->availableResources = $this->availableEndpoints[$endpoint]['resources'];

        if ($request->hasArgument('resource') === false) {
            $this->throwStatus(400);
        }

        $resource = $request->getArgument('resource');
        if (!\array_key_exists($resource, $this->availableResources)) {
            $this->throwStatus(404);
        }

        $validatedRequest = new ValidatedRequest($request);
        /** @var QueryParametersParser $parameterParser */
        $parameterParser = new QueryParametersParser($this->factory);
        $this->parameters = $parameterParser->parse($request);
        $this->validatedRequest = $validatedRequest;
        $this->registerAdapter($endpoint, $resource, $this->parameters);

        $urlPrefix = $this->getUrlPrefix($request);
        $this->encoder = $this->adapter->getEncoder($urlPrefix);
    }

    /**
     * Determines the action method and assures that the method exists.
     *
     * @return string
     * @throws NoSuchActionException
     * @throws UnsupportedRequestTypeException
     * @throws \Neos\Flow\Mvc\Exception\InvalidActionNameException
     * @throws \Neos\Flow\Mvc\Exception\InvalidActionVisibilityException
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException if the action specified in the request object does not exist (and if there's no default action either).
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     */
    protected function resolveActionMethodName()
    {
        // Default deny all
        $allowedMethods = [];
        $resource = $this->request->getArgument('resource');
        if (isset($this->availableResources[$resource]['allowedMethods'])) {
            $allowedMethods = $this->availableResources[$resource]['allowedMethods'];
        };

        if (isset($this->availableResources[$resource]['disallowedMethods'])) {
            foreach ($this->availableResources[$resource]['disallowedMethods'] as $method) {
                unset($allowedMethods[$method]);
            }
        }
//
//        if (true === $throwError && !\in_array($this->request->getHttpRequest()->getMethod(), $allowedMethods)) {
//            $this->throwStatus(403);
//        }

        if ($this->request->getControllerActionName() === 'index') {
            $actionName = 'index';
            switch ($method = $this->request->getHttpRequest()->getMethod()) {
                case 'HEAD':
                case 'GET':
                    $actionName = 'list';
                    if ($this->request->hasArgument('identifier') && $identifier = $this->request->getArgument('identifier')) {
                        $actionName = 'show';

                        $record = $this->adapter->find($this->request->getArgument('identifier'));
                        if (!$record) {
                            $this->throwStatus(404);
                        }
                        $this->record = $record;

                        if ($this->request->hasArgument('relationship')) {
                            $actionName = 'related';
                        }
                    }
                    break;
                case 'POST':
                    $actionName = 'create';
                    break;
                case 'PUT':
                case 'PATCH':
                    if (!$this->request->hasArgument('identifier')
                        && $this->request->getArgument('identifier') !== ''
                    ) {
                        $this->throwStatus(400, null, 'No resource specified');
                    }

                    $record = $this->adapter->find($this->request->getArgument('identifier'));
                    if (!$record) {
                        $this->throwStatus(404);
                    }
                    $this->record = $record;

                    $actionName = 'update';
                    break;
                case 'DELETE':
                    if (!$this->request->hasArgument('identifier')
                        && $this->request->getArgument('identifier') !== ''
                    ) {
                        $this->throwStatus(400, null, 'No resource specified');
                    }

                    $record = $this->adapter->find($this->request->getArgument('identifier'));
                    if (!$record) {
                        $this->throwStatus(404);
                    }
                    $this->record = $record;

                    $actionName = 'delete';
                    break;
                case 'OPTIONS':
                    $actionName = 'options';
                    break;
                default:
                    $this->throwStatus(403, null, 'No method allowed method specified.');
                    break;
            }

            if ($this->request->getControllerActionName() !== $actionName) {
                // Clone the request, because it should not be mutated to prevent unexpected routing behavior
                $this->request = clone $this->request;
                $this->request->setControllerActionName($actionName);
            }
        }
        return parent::resolveActionMethodName();
    }

    /**
     * @param ViewInterface $view
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     * @throws \Ttree\JsonApi\Exception\ConfigurationException
     */
    protected function initializeView(ViewInterface $view)
    {
        /** @var JsonApiView $view */
        parent::initializeView($view);
        $view->setResource($this->request->getArgument('resource'));
        $view->setEncoder($this->encoder);
        $view->setParameters($this->parameters);
    }

    /**
     * @throws \Neos\Flow\Exception
     * @return void
     */
    public function listAction()
    {
        $data = $this->adapter->query($this->parameters);
        $count = 0;
////        $data = $this->endpoint->findAll();
////        $count = $this->endpoint->countAll();
//
//        $parameters = new PaginationParameters($this->parameters->getPaginationParameters() ?: []);
//        $arguments = $this->request->getHttpRequest()->getArguments();

        if ($arguments !== []) {
            $query = \http_build_query($arguments);
            $self = new Link(\sprintf('/%s?%s', $this->adapter->getResource(), $query));
        } else {
            $self = new Link(\sprintf('/%s', $this->adapter->getResource()));
        }
        $links = [
            Link::SELF => $self
        ];

        if ($count > $parameters->getLimit()) {
            $prev = $parameters->prev();
            if ($prev !== null) {
                $query = \http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $prev));
                $links[Link::PREV] = new Link(\sprintf('/%s?%s', $this->adapter->getResource(), $query));
            }

            $next = $parameters->next($count);
            if ($next !== null) {
                $query = http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $next));
                $links[Link::NEXT] = new Link(sprintf('/%s?%s', $this->adapter->getResource(), $query));
            }

            $first = $parameters->first();
            if ($first !== null) {
                $query = http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $first));
                $links[Link::FIRST] = new Link(sprintf('/%s?%s', $this->adapter->getResource(), $query));
            }

            $last = $parameters->last($count);
            if ($last !== null) {
                $query = http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $last));
                $links[Link::LAST] = new Link(sprintf('/%s?%s', $this->adapter->getResource(), $query));
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
        $data = $this->adapter->read($identifier, $this->parameters);

        $this->view->setData($data);
    }

    /**
     * @param string $relationship
     * @throws UnsupportedRequestTypeException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @return void
     */
    public function relatedAction($relationship)
    {
        /** @var SchemaProvider $schema */
        $schema = $this->view->getSchema($this->record);
        $relationships = $schema->getRelationships($this->record, false, []);
        if (!isset($relationships[$relationship])) {
            $this->throwStatus(404, sprintf('Relationship "%s" not found', $relationship));
        }
        $this->view->setData($relationships[$relationship]['data']);
    }

    /**
     * @throws \Ttree\JsonApi\Exception\InvalidJsonException
     */
    public function createAction()
    {
        $data = $this->adapter->create($this->validatedRequest->getDocument()->getResource(), $this->parameters);

        $this->response->setStatus(201);
        $this->view->setData($data);
    }

    /**
     * @throws \Ttree\JsonApi\Exception\InvalidJsonException
     */
    public function updateAction()
    {
        $data = $this->adapter->update($this->record, $this->validatedRequest->getDocument()->getResource(), $this->parameters);

        $this->persistenceManager->persistAll();
        $this->response->setStatus(200);
        $this->view->setData($data);
    }

    /**
     * @return string
     */
    public function deleteAction()
    {
        $this->adapter->delete($this->record, $this->parameters);

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
    public function optionsAction() {
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
     * @param string $endpoint
     * @param string $resource
     * @param EncodingParametersInterface $parameters
     * @return void
     * @throws RuntimeException
     */
    protected function registerAdapter($endpoint, $resource, $parameters)
    {
        if (isset($this->availableResources[$resource]) && isset($this->availableResources[$resource]['adapter'])) {
            $adapterClass = $this->availableResources[$resource]['adapter'];
            if ($this->objectManager->isRegistered($adapterClass)) {
                $this->adapter = new $adapterClass($resource, $parameters);
            }

            throw new RuntimeException(\sprintf('Adapter %s is not registered', $adapterClass));
        }

        $this->adapter = new DefaultAdapter($endpoint, $resource, $parameters);
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    protected function getUrlPrefix(RequestInterface $request)
    {
        return rtrim($request->getMainRequest()->getHttpRequest()->getBaseUri() . $this->adapter->getBaseUrl(), '/');
    }

}
