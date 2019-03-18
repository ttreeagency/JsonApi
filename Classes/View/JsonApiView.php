<?php

namespace Flowpack\JsonApi\View;

use Neos\Flow\Annotations as Flow;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Flowpack\JsonApi\Domain\Model\ResourceSettingsDefinition;
use Neos\Flow\Mvc\View\AbstractView;
use Flowpack\JsonApi\Mvc\Controller\EncodingParametersParser;

/**
 * Class JsonApiView
 * @package Flowpack\JsonApi\View
 */
class JsonApiView extends AbstractView
{
    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var EncodingParametersParser
     */
    protected $parameters;

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->encoder->encodeData($this->data);
    }

    /**
     * @param EncodingParametersParser $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param EncoderInterface $encoder
     */
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
