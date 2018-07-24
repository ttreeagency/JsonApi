<?php
namespace Ttree\JsonApi\Integration;

//use Neomerx\JsonApi\Contracts\Integration\CurrentRequestInterface;
use Ttree\JsonApi\Exception;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;

/**
 * Exception
 *
 * @api
 */
class CurrentRequest
{

    /**
     * @var ActionRequest
     */
    protected $actionRequest;

    /**
     * CurrentRequest constructor.
     * @param ActionRequest $actionRequest
     */
    public function __construct(ActionRequest $actionRequest)
    {
        $this->actionRequest = $actionRequest;
    }

    /**
     * Get content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->actionRequest->getHttpRequest()->getContent();
    }

    /**
     * Get inputs.
     *
     * @return array
     */
    public function getQueryParameters()
    {
        $arguments = $this->actionRequest->getArguments();
        unset($arguments['resource'], $arguments['identifier'], $arguments['relationship']);
        return $arguments;
    }

    /**
     * Get header value.
     *
     * @param string $name
     *
     * @return string
     */
    public function getHeader($name)
    {
        $headers = [];
        foreach ($this->actionRequest->getHttpRequest()->getHeaders()->getAll() as $header => $value) {
            $headers[$header] = $value[0];
        }
        return isset($headers[$name]) ? $headers[$name] : '';
    }
}
