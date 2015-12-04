<?php
namespace Ttree\JsonApi\View;

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
use Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use Neomerx\JsonApi\Factories\Factory;
use Ttree\JsonApi\Integration\CurrentRequest;
use Ttree\JsonApi\Integration\ExceptionThrower;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\View\AbstractView;

/**
 * Basic REST controller for the Ttree.Medialib package
 */
class JsonApiView extends AbstractView
{
    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var Factory
     * @Flow\Inject(lazy=false)
     */
    protected $factory;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $request = $this->controllerContext->getRequest();
        if ($request instanceof ActionRequest) {
            // todo throw excetion for invalid request
        }
        $exceptionThrower = new ExceptionThrower();
        $currentRequest = new CurrentRequest($request);
        $parameterParser = $this->factory->createParametersParser();
        $parameters = $parameterParser->parse($currentRequest, $exceptionThrower);

        return $this->encoder->encodeData($this->data, $parameters);
    }

    public function setEncoder(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

}
