<?php
namespace Ttree\JsonApi\Schema;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Collections\Collection;
use \InvalidArgumentException;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Schema\SchemaProvider;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Utility\Arrays;

/**
 * Dynamic Entity Schema
 */
class DynamicEntitySchema extends SchemaProvider
{
    /**
     * @var string
     */
    protected $classType;

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $schemas;

    /**
     * @var array
     */
    protected $currentSchema;

    /**
     * @param SchemaFactoryInterface $factory
     * @param ContainerInterface $container
     * @param string $classType
     */
    public function __construct(SchemaFactoryInterface $factory, ContainerInterface $container, $classType)
    {
        $this->factory = $factory;
        $this->container = $container;
        if (trim($classType) === '') {
            throw new InvalidArgumentException('Class type can not be empty', 1449234260);
        }
        $this->classType = $classType;
    }

    public function initializeObject()
    {
        $this->schemas = $this->configurationManager->getConfiguration('JsonApiSchema');
        if (!is_array($this->schemas)) {
            throw new InvalidArgumentException('Schemas configuration not found', 1449234051);
        }
        if (!isset($this->schemas[$this->classType]) && !is_array($this->schemas[$this->classType])) {
            throw new InvalidArgumentException(sprintf('Schema for class type "%s" configuration not found', $this->classType), 1449234107);
        }
        $this->currentSchema = $this->schemas[$this->classType];
        if (!(is_string($this->currentSchema['resourceType']) === true && empty($this->currentSchema['resourceType']) === false)) {
            throw new InvalidArgumentException(sprintf('Resource type is not set for class type "%s"', $this->classType), 1449234209);
        }
        $this->resourceType = $this->currentSchema['resourceType'];

        if (!(is_string($this->currentSchema['selfSubUrl']) === true && empty($this->currentSchema['selfSubUrl']) === false)) {
            throw new InvalidArgumentException(sprintf('Resource type is not set for class type "%s"', $this->classType), 1449234209);
        }
        $this->selfSubUrl = $this->currentSchema['selfSubUrl'];
    }

    /**
     * @param object $resource
     * @return string
     */
    public function getId($resource)
    {
        return $this->persistenceManager->getIdentifierByObject($resource);
    }

    /**
     * Get resource attributes.
     *
     * @param object $resource
     *
     * @return array
     */
    public function getAttributes($resource)
    {
        $attributes = [];
        if (!isset($this->currentSchema['attributes'])) {
            throw new InvalidArgumentException(sprintf('Attributes is not configuration for class type ""', $this->classType), 1449241670);
        }
        foreach ($this->currentSchema['attributes'] as $name => $configuration) {
            $value = ObjectAccess::getPropertyPath($resource, $configuration['property']);
            if (empty($value)) {
                continue;
            }
            $attributes[$name] = $value;
        }

        return $attributes;
    }

    /**
     * @param object $resource
     * @param array $includeRelationships
     * @return array
     * @throws InvalidArgumentException
     */
    public function getRelationships($resource, array $includeRelationships = [])
    {
        $relationships = [];
        if (!isset($this->currentSchema['relationships'])) {
            return parent::getRelationships($resource, $includeRelationships);
        }
        foreach ($this->currentSchema['relationships'] as $name => $configuration) {
            $property = $configuration['data']['property'];
            if (!ObjectAccess::isPropertyGettable($resource, $property)) {
                throw new InvalidArgumentException(sprintf('The path "%s" is not gettable in the current resource of type "%s"', $property, $this->classType), 1449241448);
            }
            $value = ObjectAccess::getPropertyPath($resource, $property);
            if ($value instanceof Collection) {
                $value = $value->toArray();
            }
            $relationships[$name] = [
                self::DATA => $value,
                self::SHOW_RELATED => isset($configuration['showRelated']) && $configuration['showRelated'] === true,
                self::SHOW_SELF => isset($configuration['showSelf']) && $configuration['showSelf'] === true
            ];
        }

        return $relationships;
    }

    /**
     * @return array
     */
    public function getIncludePaths()
    {
        $includePaths = Arrays::getValueByPath($this->currentSchema, 'includePaths');
        if ($includePaths === null) {
            return parent::getIncludePaths();
        }
        return array_keys(array_filter($includePaths));
    }
}
