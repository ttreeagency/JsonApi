<?php
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
     * @return string
     */
    public function getResource();

    /**
     * @param string $identifier
     * @return object
     */
    public function findByIdentifier($identifier);

    /**
     * @return \Neos\Flow\Persistence\QueryResultInterface
     */
    public function findAll();
}
