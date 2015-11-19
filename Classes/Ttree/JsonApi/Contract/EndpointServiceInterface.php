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

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;

/**
 * EndpointService Interface
 *
 * @api
 */
interface EndpointServiceInterface
{
    /**
     * @param string|null $urlPrefix
     * @param integer $depth
     * @return EncoderInterface
     */
    public function getEncoder($urlPrefix = null, $depth = 512);

    /**
     * @param string $identifier
     * @return object
     */
    public function findByIdentifier($identifier);

    /**
     * @param integer $offset
     * @param integer $limit
     * @return \TYPO3\Flow\Persistence\QueryResultInterface
     */
    public function findAll($offset = 0, $limit = 25);
}
