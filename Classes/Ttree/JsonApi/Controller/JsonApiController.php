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

use Ttree\JsonApi\Service\EndpointService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\Exception\NoSuchActionException;

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
     * @var \Ttree\JsonApi\View\JsonApiView
     */
    protected $view;

    /**
     * @var EndpointService
     */
    protected $endpoint;

    protected function initializeAction()
    {
        parent::initializeAction();
        $this->response->setHeader('Content-Type', 'application/vnd.api+json');
    }

    /**
     * Determines the action method and assures that the method exists.
     *
     * @return string The action method name
     * @throws NoSuchActionException if the action specified in the request object does not exist (and if there's no default action either).
     */
    protected function resolveActionMethodName()
    {
        if ($this->request->hasArgument('resource') === false) {
            $this->throwStatus(400);
        }
        $this->endpoint = new EndpointService($this->request->getArgument('resource'));
        if ($this->request->getControllerActionName() === 'index') {
            $actionName = 'index';
            switch ($this->request->getHttpRequest()->getMethod()) {
                case 'HEAD':
                case 'GET':
                    if ($this->request->hasArgument('resource') && $this->request->hasArgument('identifier')) {
                        $actionName = 'show';
                    } else {
                        $actionName = 'index';
                    }
                    break;
                case 'POST':
                case 'PUT':
                case 'DELETE':
                    throw new NoSuchActionException('Not implemented', 1447800455);
                    break;
            }
            $this->request->setControllerActionName($actionName);
        }
        return parent::resolveActionMethodName();
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $data = $this->endpoint->findAll()->toArray();

        $encoder = $this->endpoint->getEncoder($this->getUrlPrefix());

        $this->view->setEncoder($encoder);
        $this->view->setData($data);
    }

    /**
     * @param string $identifier
     * @return void
     */
    public function showAction($identifier)
    {
        $data = $this->endpoint->findByIdentifier($identifier);

        $encoder = $this->endpoint->getEncoder($this->getUrlPrefix());

        $this->view->setEncoder($encoder);
        $this->view->setData($data);
    }

    /**
     * @return string
     */
    protected function getUrlPrefix() {
        return rtrim($this->request->getMainRequest()->getHttpRequest()->getBaseUri() . $this->endpoint->getBaseUrl(), '/');
    }

}
