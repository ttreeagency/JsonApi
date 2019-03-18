<?php

namespace Flowpack\JsonApi\Mvc\Routing;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Routing\AbstractRoutePart;
use Neos\Flow\Mvc\Routing\Dto\MatchResult;
use Neos\Flow\Mvc\Routing\Dto\RouteParameters;
use Neos\Flow\Mvc\Routing\Dto\ResolveResult;
use Neos\Flow\Mvc\Routing\DynamicRoutePartInterface;
use Neos\Flow\Mvc\Routing\ParameterAwareRoutePartInterface;
use Flowpack\JsonApi\Exception\ConfigurationException;

/**
 * Class ModelNameRoutePart
 * @package Flowpack\JsonApi\Routing
 */
abstract class AbstractRoutePartHandler extends AbstractRoutePart implements DynamicRoutePartInterface, ParameterAwareRoutePartInterface
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $availableEndpoints;

    /**
     * The split string represents the end of a Dynamic Route Part.
     * If it is empty, Route Part will be equal to the remaining request path.
     *
     * @var string
     */
    protected $splitString = '';

    /**
     * The Routing RouteParameters passed to matchWithParameters()
     * These allow sub classes to adjust the matching behavior accordingly
     *
     * @var RouteParameters
     */
    protected $parameters;

    /**
     * Sets split string of the Route Part.
     *
     * @param string $splitString
     * @return void
     * @api
     */
    public function setSplitString($splitString)
    {
        $this->splitString = $splitString;
    }

    /**
     * Inject the settings
     * @param array $settings
     * @throws ConfigurationException
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
        if (!isset($this->settings['endpoints'])) {
            throw new ConfigurationException('Missing Endpoints configuration.');
        }

        $this->availableEndpoints = $this->settings['endpoints'];
    }

    /**
     * Checks whether $routeValues contains elements which correspond to this Dynamic Route Part.
     * If a corresponding element is found in $routeValues, this element is removed from the array.
     *
     * @param array $routeValues An array with key/value pairs to be resolved by Dynamic Route Parts.
     * @return bool|ResolveResult true or an instance of ResolveResult if current Route Part could be resolved, otherwise false
     */
    public function resolve(array &$routeValues)
    {
        return false;
    }

    /**
     * Removes matching part from $routePath.
     * This method can be overridden by custom RoutePartHandlers to implement custom matching mechanisms.
     *
     * @param string $routePath The request path to be matched
     * @param string $valueToMatch The matching value
     * @return void
     * @api
     */
    protected function removeMatchingPortionFromRequestPath(&$routePath, $valueToMatch)
    {
        if ($valueToMatch !== null && $valueToMatch !== '') {
            $routePath = \substr($routePath, \strlen($valueToMatch));
        }
    }

    /**
     * Checks whether this Dynamic Route Part corresponds to the given $routePath.
     *
     * @see matchWithParameters()
     *
     * @param string $routePath The request path to be matched - without query parameters, host and fragment.
     * @return bool|MatchResult true or an instance of MatchResult if Route Part matched $routePath, otherwise false.
     */
    final function match(&$routePath)
    {
        return $this->matchWithParameters($routePath, RouteParameters::createEmpty());
    }

    /**
     * @param string $routePath
     * @param RouteParameters $parameters
     * @return bool
     */
    public function matchWithParameters(&$routePath, RouteParameters $parameters)
    {
        $this->value = null;
        $this->parameters = $parameters;
        if ($this->name === null || $this->name === '') {
            return false;
        }

        $valueToMatch = $this->findValueToMatch($routePath);
        $matchResult = $this->matchValue($valueToMatch);

        if ($matchResult !== true && !($matchResult instanceof MatchResult)) {
            return $matchResult;
        }
        $this->removeMatchingPortionFromRequestPath($routePath, $valueToMatch[0]);

        return $matchResult;
    }

    /**
     * Returns the first part of $routePath.
     * If a split string is set, only the first part of the value until location of the splitString is returned.
     * This method can be overridden by custom RoutePartHandlers to implement custom matching mechanisms.
     *
     * @param string $routePath The request path to be matched
     * @return string value to match, or an empty string if $routePath is empty or split string was not found
     * @api
     */
    protected function findValueToMatch($routePath)
    {
        if (!isset($routePath) || $routePath === '' || $routePath[0] === '/') {
            return '';
        }
        $valuesToMatch = $routePath;
        if ($this->splitString !== '') {
            $valuesToMatch = \explode($this->splitString, $routePath);
        }

        return $valuesToMatch;
    }

    /**
     * @param string $value
     * @return boolean
     */
    protected function matchValue($value)
    {
        if ($value === null || $value === '') {
            return false;
        }
        foreach ($this->availableEndpoints as $endpointKey => $endpointConfiguration) {
            if (isset($endpointConfiguration['baseUrl'])
                && $endpointConfiguration['baseUrl'] === $value[0]
                && isset($endpointConfiguration['version'])
                && $endpointConfiguration['version'] === $value[1]
                && isset($endpointConfiguration['resources'])
                && \array_key_exists($value[2], $endpointConfiguration['resources'])
                && $this->name === '@endpoint') {
                $this->value = $endpointConfiguration;
                return true;
            }
        }

        return false;
    }
}