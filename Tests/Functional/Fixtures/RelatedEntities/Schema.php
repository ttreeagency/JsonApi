<?php

namespace Flowpack\JsonApi\Tests\Functional\Fixtures\RelatedEntities;

use Neos\Flow\Annotations as Flow;
use Neomerx\JsonApi\Schema\BaseSchema;
use Neos\Flow\Tests\Functional\Persistence\Fixtures\TestEntity;
use SZ\SocialSmartz\Domain\Model\Post;
use Flowpack\JsonApi\Domain\Model\JsonApiSchemaDefinition;
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
    protected $resourceType = 'Neos\Flow\Tests\Functional\Persistence\Fixtures\TestEntity';

    /**
     * @var string
     */
    protected $type = 'entities';

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

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
     * Get resource attributes.
     *
     * @param TestEntity $resource
     *
     * @return array
     */
    public function getAttributes($resource): iterable
    {
        $attributes = [
            'name' => $resource->getName(),
            'description' => $resource->getDescription(),
        ];

        return $attributes;
    }

    /**
     * @param Post $resource
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
