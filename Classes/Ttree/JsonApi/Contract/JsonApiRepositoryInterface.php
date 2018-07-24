<?php
namespace Ttree\JsonApi\Contract;

use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Ttree\JsonApi\Domain\Model\ResourceSettingsDefinition;

/**
 * JsonApiPaginate Interface
 *
 * @api
 */
interface JsonApiRepositoryInterface
{
    public function findByJsonApiParameters(EncodingParametersInterface $parameters, ResourceSettingsDefinition $resourceSettingsDefinition);

    public function findByIdentifier($identifier);

    public function countAll();
}
