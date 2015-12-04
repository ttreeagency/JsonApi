<?php
/**
 * This script belongs to the TYPO3 Flow package "medialib.tv"
 *
 * Check the LICENSE.txt for more informations about the license
 * used for this project
 *
 * Hand crafted with love to each detail by ttree.ch
 */
namespace Ttree\JsonApi\Contract;

use Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use Ttree\JsonApi\Domain\Model\ResourceSettingsDefinition;

/**
 * JsonApiPaginate Interface
 *
 * @api
 */
interface JsonApiRepositoryInterface
{
    public function findByJsonApiParameters(ParametersInterface $parameters, ResourceSettingsDefinition $resourceSettingsDefinition);

    public function findByIdentifier($identifier);
}
