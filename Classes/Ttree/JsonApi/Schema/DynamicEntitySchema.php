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
use InvalidArgumentException;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Schema\SchemaProvider;
use Ttree\JsonApi\Domain\Model\JsonApiSchemaDefinition;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * Dynamic Entity Schema
 */
class DynamicEntitySchema extends SchemaProvider
{
    /**
     * @var JsonApiSchemaDefinition
     */
    protected $schemaDefinition;

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * @param SchemaFactoryInterface $factory
     * @param ContainerInterface $container
     * @param string $classType
     */
    public function __construct(SchemaFactoryInterface $factory, ContainerInterface $container, $classType)
    {
        $this->resourceType = $classType;
        $this->schemaDefinition = new JsonApiSchemaDefinition($classType);
        $this->selfSubUrl = $this->schemaDefinition->getSelfSubUrl();

        parent::__construct($factory, $container);
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
     * @return string
     */
    public function getSelfSubUrl()
    {
        return $this->schemaDefinition->getSelfSubUrl();
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return $this->schemaDefinition->getResourceType();
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
        foreach ($this->schemaDefinition->getAttributes() as $name => $configuration) {
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
        foreach ($this->schemaDefinition->getRelationships() as $name => $configuration) {
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
        $includePaths = $this->schemaDefinition->getIncludePaths();
        if ($includePaths === []) {
            return $includePaths;
        }
        return array_keys(array_filter($includePaths));
    }
}
