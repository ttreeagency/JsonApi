<?php
namespace Ttree\JsonApi\Controller;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use Neomerx\JsonApi\Schema\Link;
use Neomerx\JsonApi\Schema\SchemaProvider;
use Ttree\JsonApi\Domain\Model\PaginationParameters;
use Ttree\JsonApi\Integration\CurrentRequest;
use Ttree\JsonApi\Integration\ExceptionThrower;
use Ttree\JsonApi\Service\EndpointService;
use Ttree\JsonApi\View\JsonApiView;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\Flow\Mvc\RequestInterface;
use TYPO3\Flow\Mvc\ResponseInterface;
use TYPO3\Flow\Mvc\View\ViewInterface;
use TYPO3\Flow\Utility\Arrays;

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
     * @var ParametersInterface
     */
    protected $parameters;

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
     */
    protected function initializeController(RequestInterface $request, ResponseInterface $response)
    {
        parent::initializeController($request, $response);

        /** @var ActionRequest $request */
        if ($request->hasArgument('resource') === false) {
            $this->throwStatus(400);
        }
        // todo return error is the resource is not found or invalid
        $resource = $request->getArgument('resource');

        $exceptionThrower = new ExceptionThrower();
        $currentRequest = new CurrentRequest($request);
        $parameterParser = $this->factory->createParametersParser();
        $this->parameters = $parameterParser->parse($currentRequest, $exceptionThrower);

        $this->endpoint = new EndpointService($resource, $this->parameters);

        $urlPrefix = $this->getUrlPrefix($request);
        $this->encoder = $this->endpoint->getEncoder($urlPrefix);
    }

    protected function initializeView(ViewInterface $view)
    {
        /** @var JsonApiView $view */
        parent::initializeView($view);
        $view->setResource($this->request->getArgument('resource'));
        $view->setEncoder($this->encoder);
        $view->setParameters($this->parameters);
    }


    /**
     * @return void
     */
    public function indexAction()
    {
        $data = $this->endpoint->findAll();
        $count = $this->endpoint->countAll();

        $parameters = new PaginationParameters($this->parameters->getPaginationParameters() ?: []);
        $arguments = $this->request->getHttpRequest()->getArguments();

        $links = [
            Link::SELF => new Link(sprintf('/%s', $this->endpoint->getResource()))
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

        $this->encoder
            ->withLinks($links);

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
     * @param string $identifier
     * @param string $relationship
     * @return void
     */
    public function relatedAction($identifier, $relationship)
    {
        $data = $this->endpoint->findByIdentifier($identifier);
        /** @var SchemaProvider $schema */
        $schema = $this->view->getSchema($data);
        $relationships = $schema->getRelationships($data);
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

}
