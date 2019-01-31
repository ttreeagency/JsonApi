<?php

namespace Ttree\JsonApi\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 *
 * Class DefaultRepository
 * @package Ttree\JsonApi\Domain\Repository
 */
class DefaultRepository extends Repository
{

    use JsonApiRepositoryTrait;

    /**
     * Initializes a dynamic Repository.
     */
    public function __construct()
    {
    }

    /**
     * Set the classname of the entities this repository is managing.
     * Note that anything that is an "instanceof" this class is accepted
     * by the repository.
     *
     * @api
     * @param string $entityClassName
     */
    public function setEntityClassName($entityClassName)
    {
        $this->entityClassName = preg_replace(array('/\\\Repository\\\/', '/Repository$/'), array('\\Model\\', ''), $entityClassName);
    }
}