<?php
namespace Ttree\JsonApi\Controller;

use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neos\Flow\Annotations as Flow;
use Ttree\JsonApi\Mvc\Controller\QueryParametersParser;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Schema\SchemaProvider;
use Ttree\JsonApi\Domain\Model\PaginationParameters;
use Ttree\JsonApi\Integration\CurrentRequest;
use Ttree\JsonApi\Service\EndpointService;
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
     * @var EndpointService
     */
    protected $endpoint;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var FactoryInterface
     * @Flow\Inject(lazy=false)
     */
    protected $factory;

    /**
     * @var EncodingParametersInterface
     */
    protected $parameters;

    /**
     * @Flow\InjectConfiguration(path="endpoints.default.resources")
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
     */
    protected function initializeController(RequestInterface $request, ResponseInterface $response)
    {
        parent::initializeController($request, $response);

        /** @var ActionRequest $request */
        if ($request->hasArgument('resource') === false) {
            $this->throwStatus(400);
        }

        $resource = $request->getArgument('resource');
        if (!\array_key_exists($resource, $this->availableResources)) {
            $this->throwStatus(404);
        }

        $currentRequest = new CurrentRequest($request);
        /** @var QueryParametersParser $parameterParser */
        $parameterParser = new QueryParametersParser($this->factory);
        $this->parameters = $parameterParser->parse($currentRequest);

        $this->endpoint = new EndpointService($resource, $this->parameters);

        $urlPrefix = $this->getUrlPrefix($request);
        $this->encoder = $this->endpoint->getEncoder($urlPrefix);
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
    public function indexAction()
    {
        if ($this->request->hasArgument('page') === false) {
            $this->request->setArgument('page', [
                'number' => 1,
                'size' => $this->settings['pagination']['defaultPageSize']
            ]);
        }

        $data = $this->endpoint->findAll();
        $count = $this->endpoint->countAll();

        $parameters = new PaginationParameters($this->parameters->getPaginationParameters() ?: []);
        $arguments = $this->request->getHttpRequest()->getArguments();

        if ($arguments !== []) {
            $query = http_build_query($arguments);
            $self = new Link(sprintf('/%s?%s', $this->endpoint->getResource(), $query));
        } else {
            $self = new Link(sprintf('/%s', $this->endpoint->getResource()));
        }
        $links = [
            Link::SELF => $self
        ];

        if ($count > $parameters->getLimit()) {
            $prev = $parameters->prev();
            if ($prev !== null) {
                $query = http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $prev));
                $links[Link::PREV] = new Link(sprintf('/%s?%s', $this->endpoint->getResource(), $query));
            }

            $next = $parameters->next($count);
            if ($next !== null) {
                $query = http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $next));
                $links[Link::NEXT] = new Link(sprintf('/%s?%s', $this->endpoint->getResource(), $query));
            }

            $first = $parameters->first();
            if ($first !== null) {
                $query = http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $first));
                $links[Link::FIRST] = new Link(sprintf('/%s?%s', $this->endpoint->getResource(), $query));
            }

            $last = $parameters->last($count);
            if ($last !== null) {
                $query = http_build_query(Arrays::arrayMergeRecursiveOverrule($arguments, $last));
                $links[Link::LAST] = new Link(sprintf('/%s?%s', $this->endpoint->getResource(), $query));
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
        $data = $this->endpoint->findByIdentifier($identifier);

        $this->view->setData($data);
    }

    /**
     * @param $identifier
     * @param $relationship
     * @throws UnsupportedRequestTypeException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @return void
     */
    public function relatedAction($identifier, $relationship)
    {
        $data = $this->endpoint->findByIdentifier($identifier);
        /** @var SchemaProvider $schema */
        $schema = $this->view->getSchema($data);
        $relationships = $schema->getRelationships($data, false, []);
        if (!isset($relationships[$relationship])) {
            $this->throwStatus(404, sprintf('Relationship "%s" not found', $relationship));
        }
        $this->view->setData($relationships[$relationship]['data']);
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    protected function getUrlPrefix(RequestInterface $request)
    {
        return rtrim($request->getMainRequest()->getHttpRequest()->getBaseUri() . $this->endpoint->getBaseUrl(), '/');
    }

    /**
     * @param $model
     */
    public function createAction($model)
    {
        $this->endpoint->add($model);
        $this->persistenceManager->persistAll();
        $this->response->setStatus(201);
        $this->view->setData($model);
    }

    /**
     * @param $identifier
     * @param $data
     */
    public function updateAction($identifier, $data)
    {
        $model = $this->endpoint->findByIdentifier($identifier);

        // DO UPDATE STUFF
        $this->endpoint->update($model);

        $this->persistenceManager->persistAll();
        $this->response->setStatus(200);
        $this->view->setData($model);
    }

    /**
     * @param $identifier
     * @return string
     */
    public function deleteAction($identifier)
    {
        $model = $this->endpoint->findByIdentifier($identifier);
        $this->endpoint->remove($model);

        $this->persistenceManager->persistAll();
        $this->response->setStatus(204);
        return '';
    }

    /**
     * Returns the supported request methods for a single and set the "Allow" header accordingly
     *
     * @return string An empty string in order to prevent the view from rendering the action
     * @throws \Neos\Flow\Mvc\Exception\NoSuchArgumentException
     */
    public function optionsAction() {

        $allowedMethods = array(
            'GET',
            'POST',
            'PATCH',
            'DELETE'
        );
        $resource = $this->request->getArgument('resource');

        if (isset($this->availableResources[$resource]['allowedMethods'])) {
            $allowedMethods = $this->availableResources[$resource]['allowedMethods'];
        };

        if (isset($this->availableResources[$resource]['disallowedMethods'])) {
            foreach ($this->availableResources[$resource]['disallowedMethods'] as $method) {
                unset($allowedMethods[$method]);
            }
        };

        $this->response->setHeader('Access-Control-Allow-Methods', implode(', ', array_unique($allowedMethods)));
        $this->response->setStatus(204);
        return '';
    }

}
