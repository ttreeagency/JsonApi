<?php

namespace Flowpack\JsonApi\Mvc\Routing;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Routing\AbstractRoutePart;
use Neos\Flow\Mvc\Routing\Dto\MatchResult;
use Neos\Flow\Mvc\Routing\Dto\RouteParameters;
use Neos\Flow\Mvc\Routing\DynamicRoutePart;
use Neos\Flow\Mvc\Routing\DynamicRoutePartInterface;
use Neos\Flow\Mvc\Routing\ParameterAwareRoutePartInterface;
use Flowpack\JsonApi\Exception\ConfigurationException;

/**
 * Class ModelNameRoutePart
 * @package Flowpack\JsonApi\Routing
 */
class VersionRoutePartHandler extends AbstractRoutePartHandler
{

    /**
     * @param string $value
     * @return boolean
     */
    protected function matchValue($value)
    {
        if ($value === null || $value === '') {
            return false;
        }
        if ($this->name === '@version') {
            return true;
        }

        return false;
    }
}