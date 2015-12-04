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
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Schema\Link;
use Neomerx\JsonApi\Schema\SchemaProvider;
use Ttree\JsonApi\Domain\Model\ResourceSettingsDefinition;
use Ttree\JsonApi\Service\EndpointService;
use Ttree\JsonApi\View\JsonApiView;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\Flow\Mvc\RequestInterface;
use TYPO3\Flow\Mvc\ResponseInterface;
use TYPO3\Flow\Mvc\View\ViewInterface;

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
     * @var ContainerInterface
     * @Flow\Inject(lazy=false)
     */
    protected $container;

    /**
     * @var EndpointService
     */
    protected $endpoint;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

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
        $resource = $request->getArgument('resource');
        $resourceSettingsDefinition = new ResourceSettingsDefinition($resource);
        $this->container->registerArray($resourceSettingsDefinition->getSchemas());

        $this->endpoint = new EndpointService($resource);

        $urlPrefix = $this->getUrlPrefix($request);
        $this->encoder = $this->endpoint->getEncoder($urlPrefix);
    }

    protected function initializeView(ViewInterface $view)
    {
        /** @var JsonApiView $view */
        parent::initializeView($view);
        $view->setEncoder($this->encoder);
    }


    /**
     * @return void
     */
    public function indexAction()
    {
        $links = [
            Link::SELF => new Link(sprintf('/%s', $this->endpoint->getResource())),
        ];

        $data = $this->endpoint->findAll()->toArray();

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
        $schema = $this->container->getSchema($data);
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
