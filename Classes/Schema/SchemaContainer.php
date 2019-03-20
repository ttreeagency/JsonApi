<?php
namespace Flowpack\JsonApi\Schema;

use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;

/**
 * Class SchemaContainer
 * @package Flowpack\JsonApi\Schema
 */
class SchemaContainer extends \Neomerx\JsonApi\Schema\SchemaContainer {

    /**
     * @var array
     */
    protected $providerMapping = [];

    /**
     * @var SchemaInterface[]
     */
    protected $createdProviders = [];

    /**
     * @var array
     */
    protected $resType2JsonType = [];

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @param FactoryInterface $factory
     * @param iterable         $schemas
     */
    public function __construct(FactoryInterface $factory, iterable $schemas)
    {
        parent::__construct($factory, []);
        $this->factory = $factory;
        $this->registerCollection($schemas);
    }

    /**
     * @inheritdoc
     */
    public function hasSchema($resourceObject): bool
    {
        if (\is_object($resourceObject) === true) {
            $type = $this->getResourceType($resourceObject);
            return $this->hasProviderMapping($type) === true || $this->isProxy($type) === true;
        }
        return false;
    }

    /**
     * @param $type
     * @return bool
     */
    protected function isProxy($type): bool
    {
        if ($type === '') {
            return false;
        }

        foreach ($this->providerMapping as $providerType => $schema) {
            if (\strpos($type, $providerType)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $type
     * @return string
     */
    protected function getSchemaByProxy($type): string
    {
        foreach ($this->providerMapping as $providerType => $schema) {
            if (\strpos($type, $providerType)) {
                return $schema;
            }
        }

        throw new InvalidArgumentException(_(static::MSG_INVALID_SCHEME, $type));
    }

    /**
     * Register provider for resource type.
     *
     * @param string $type
     * @param string|Closure $schema
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function register(string $type, $schema): void
    {
        if (empty($type) === true || \class_exists($type) === false) {
            throw new InvalidArgumentException(_(static::MSG_INVALID_MODEL_TYPE));
        }

        $isOk = (
            (
                \is_string($schema) === true &&
                empty($schema) === false &&
                \class_exists($schema) === true &&
                \in_array(SchemaInterface::class, \class_implements($schema)) === true
            ) ||
            \is_callable($schema) ||
            $schema instanceof SchemaInterface
        );
        if ($isOk === false) {
            throw new InvalidArgumentException(_(static::MSG_INVALID_SCHEME, $type));
        }

        if ($this->hasProviderMapping($type) === true) {
            throw new InvalidArgumentException(_(static::MSG_TYPE_REUSE_FORBIDDEN, $type));
        }

        if ($schema instanceof SchemaInterface) {
            $this->setProviderMapping($type, \get_class($schema));
            $this->setResourceToJsonTypeMapping($schema->getType(), $type);
            $this->setCreatedProvider($type, $schema);
        } else {
            $this->setProviderMapping($type, $schema);
        }
    }

    /**
     * Register providers for resource types.
     *
     * @param iterable $schemas
     *
     * @return void
     */
    public function registerCollection(iterable $schemas): void
    {
        foreach ($schemas as $type => $schema) {
            $this->register($type, $schema);
        }
    }

    /**
     * @inheritdoc
     */
    public function getSchema($resource): SchemaInterface
    {
        $resourceType = $this->getResourceType($resource);

        return $this->getSchemaByType($resourceType);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function getSchemaByType(string $type): SchemaInterface
    {
        if ($this->hasCreatedProvider($type) === true) {
            return $this->getCreatedProvider($type);
        }

        if ($this->isProxy($type)) {
            $classNameOrCallable = $this->getSchemaByProxy($type);
        } else {
            $classNameOrCallable = $this->getProviderMapping($type);
        }

        if (\is_string($classNameOrCallable) === true) {
            $schema = $this->createSchemaFromClassName($classNameOrCallable);
        } else {
            \assert(\is_callable($classNameOrCallable) === true);
            $schema = $this->createSchemaFromCallable($classNameOrCallable);
        }
        $this->setCreatedProvider($type, $schema);

        /** @var SchemaInterface $schema */

        $this->setResourceToJsonTypeMapping($schema->getType(), $type);

        return $schema;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function hasProviderMapping(string $type): bool
    {
        return isset($this->providerMapping[$type]);
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    protected function getProviderMapping(string $type)
    {
        return $this->providerMapping[$type];
    }

    /**
     * @param string $type
     * @param string|Closure $schema
     *
     * @return void
     */
    protected function setProviderMapping(string $type, $schema): void
    {
        $this->providerMapping[$type] = $schema;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function hasCreatedProvider(string $type): bool
    {
        return isset($this->createdProviders[$type]);
    }

    /**
     * @param string $type
     *
     * @return SchemaInterface
     */
    protected function getCreatedProvider(string $type): SchemaInterface
    {
        return $this->createdProviders[$type];
    }

    /**
     * @param string $type
     * @param SchemaInterface $provider
     *
     * @return void
     */
    protected function setCreatedProvider(string $type, SchemaInterface $provider): void
    {
        $this->createdProviders[$type] = $provider;
    }

    /**
     * @param string $resourceType
     * @param string $jsonType
     *
     * @return void
     */
    protected function setResourceToJsonTypeMapping(string $resourceType, string $jsonType): void
    {
        $this->resType2JsonType[$resourceType] = $jsonType;
    }

    /**
     * @param object $resource
     *
     * @return string
     */
    protected function getResourceType($resource): string
    {
        \assert(
            \is_object($resource) === true,
            'Unable to get a type of the resource as it is not an object.'
        );

        return \get_class($resource);
    }

    /**
     * @param callable $callable
     *
     * @return SchemaInterface
     */
    protected function createSchemaFromCallable(callable $callable): SchemaInterface
    {
        $schema = \call_user_func($callable, $this->factory);

        return $schema;
    }

    /**
     * @param string $className
     *
     * @return SchemaInterface
     */
    protected function createSchemaFromClassName(string $className): SchemaInterface
    {
        $schema = new $className($this->factory);

        return $schema;
    }
}