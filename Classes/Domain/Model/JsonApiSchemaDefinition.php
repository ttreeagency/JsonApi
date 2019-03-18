<?php
namespace Flowpack\JsonApi\Domain\Model;

use Neos\Flow\Annotations as Flow;
use InvalidArgumentException;
use Neos\Flow\Configuration\ConfigurationManager;

/**
 * JSON API Schema Definition
 */
class JsonApiSchemaDefinition
{
    /**
     * @var string
     */
    protected $classType;

    /**
     * @var ConfigurationManager
     * @Flow\Inject
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
     * @var string
     */
    protected $resourceType;

    /**
     * @var string
     */
    protected $selfSubUrl;

    /**
     * JsonApiSchemaDefinition constructor.
     * @param string $classType
     */
    public function __construct($classType)
    {
        if (trim($classType) === '') {
            throw new InvalidArgumentException('Class type can not be empty', 1449234260);
        }

        $this->classType = $classType;
    }

    /**
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationTypeException
     */
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

        if (!isset($this->currentSchema['attributes']) || !is_array($this->currentSchema['attributes'])) {
            throw new InvalidArgumentException(sprintf('Attributes is not configuration for class type "%s"', $this->classType), 1449241670);
        }
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        return $this->currentSchema;
    }

    /**
     * @return string
     */
    public function getSelfSubUrl()
    {
        return $this->selfSubUrl;
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->currentSchema['attributes'];
    }

    /**
     * @return array
     */
    public function getRelationships()
    {
        return isset($this->currentSchema['relationships']) ? $this->currentSchema['relationships'] : [];
    }

    /**
     * @return array
     */
    public function getIncludePaths()
    {
        return isset($this->currentSchema['includePaths']) ? $this->currentSchema['includePaths'] : [];
    }
}
