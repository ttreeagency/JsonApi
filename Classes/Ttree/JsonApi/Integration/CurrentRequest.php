<?php
namespace Ttree\JsonApi\Integration;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neomerx\JsonApi\Contracts\Integration\CurrentRequestInterface;
use Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;
use Ttree\JsonApi\Exception;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\ActionRequest;

/**
 * Exception
 *
 * @api
 */
class CurrentRequest implements CurrentRequestInterface
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
