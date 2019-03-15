<?php

namespace Ttree\JsonApi\JsonApi\PersistentResource;

use Neos\Flow\Annotations as Flow;
use Neomerx\JsonApi\Schema\BaseSchema;
use Neos\Flow\ResourceManagement\PersistentResource;
use SZ\SocialSmartz\Domain\Model\Post;
use Ttree\JsonApi\Domain\Model\JsonApiSchemaDefinition;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * Entity SchemaProvider
 * @Flow\Scope("singleton")
 */
class Schema extends BaseSchema
{

    /**
     * @var string
     */
    protected $resourceType = 'Neos\Flow\ResourceManagement\PersistentResource';

    /**
     * @var string
     */
    protected $type = 'resources';

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * @var JsonApiSchemaDefinition
     */
    protected $schemaDefinition;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
        return \sprintf('/%s/%s', $this->type, $this->getId($resource));
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
     * @param PersistentResource $resource
     *
     * @return array
     */
    public function getAttributes($resource): iterable
    {
        $attributes = [
            'filename' => $resource->getFilename(),
            'file-extension' => $resource->getFileExtension(),
            'file-size' => $resource->getFileSize(),
            'media-type' => $resource->getMediaType(),
            'url' => $resource->getRelativePublicationPath(),
        ];

        return $attributes;
    }

    /**
     * @param PersistentResource $resource
     * @return array
     */
    public function getRelationships($resource): iterable
    {
        return [];
    }

    /**
     * @return array
     */
    public function getIncludePaths()
    {
        return [];
    }
}
