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

use Ttree\JsonApi\Domain\Model\PaginateOptions;

/**
 * JsonApiPaginate Interface
 *
 * @api
 */
interface JsonApiPaginateInterface
{
    public function paginate(PaginateOptions $options);

    public function findByIdentifier($identifier);
}
