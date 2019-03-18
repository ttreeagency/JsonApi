<?php

namespace Flowpack\JsonApi\Schema;

use Neos\Flow\Annotations as Flow;
use Neomerx\JsonApi\Schema\BaseSchema;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Flowpack\JsonApi\Domain\Model\JsonApiSchemaDefinition;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Utility\ObjectAccess;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Media\Domain\Model\AssetInterface;

/**
 * Dynamic Entity Schema
 */
class ResourceSchema extends BaseSchema
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
     * @var PersistentResource
     * @Flow\Inject
     */
    protected $resourcePublisher;

    /**
     * @return string
     */
    public function getType(): string
    {
        // TODO: Implement getType() method.
    }

    /**
     * @param object $resource
     * @return string
     */
    public function getId($resource): string
    {
        return $this->persistenceManager->getIdentifierByObject($resource);
    }

    /**
     * @param null $resource
     * @return string
     */
    public function getSelfSubUrl($resource = null): string
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
    public function getAttributes($resource): iterable
    {
        $attributes = [];
        foreach ($this->schemaDefinition->getAttributes() as $name => $configuration) {
            $value = ObjectAccess::getPropertyPath($resource, $configuration['property']);
            if ($value instanceof AssetInterface) {
                $value = $this->resourcePublisher->getPersistentResourceWebUri($value->getResource());
            }
            if (empty($value)) {
                continue;
            }
            $attributes[$name] = $value;
        }

        return $attributes;
    }

    /**
     * @param object $resource
     * @return array
     */
    public function getRelationships($resource): iterable
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
        return \array_keys(\array_filter($includePaths));
    }
}
