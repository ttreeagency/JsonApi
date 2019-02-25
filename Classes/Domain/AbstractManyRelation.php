<?php

namespace Ttree\JsonApi\Domain;

use Doctrine\ORM\Mapping\Entity;
use Ttree\JsonApi\Adapter\AbstractAdapter;
use Ttree\JsonApi\Exception\RuntimeException;
use Ttree\JsonApi\Contract\Adapter\HasManyAdapterInterface;
use Ttree\JsonApi\Contract\Parameters\EncodingParametersInterface;

/**
 * Class AbstractManyRelation
 *
 * @package CloudCreativity\LaravelJsonApi
 */
abstract class AbstractManyRelation extends AbstractRelation implements HasManyAdapterInterface
{

    /**
     * @param Model $record
     * @param EncodingParametersInterface $parameters
     * @return mixed
     */
    public function query($record, EncodingParametersInterface $parameters)
    {
        /** If we do not need to pass to the inverse adapter, we can just return the whole relationship. */
        if (!$this->requiresInverseAdapter($record, $parameters)) {
            return $record->{$this->key};
        }

        $relation = $this->getRelation($record);
        $adapter = $this->store()->adapterFor($relation->getModel());

        if (!$adapter instanceof AbstractAdapter) {
            throw new RuntimeException('Expecting inverse adapter to be an Doctrine adapter.');
        }

        return $adapter->queryRelation($relation, $parameters);
    }

    /**
     * @param Model $record
     * @param EncodingParametersInterface $parameters
     * @return mixed
     */
    public function relationship($record, EncodingParametersInterface $parameters)
    {
        return $this->query($record, $parameters);
    }

    /**
     * Does the query need to be passed to the inverse adapter?
     *
     * @param $record
     * @param EncodingParametersInterface $parameters
     * @return bool
     */
    protected function requiresInverseAdapter($record, EncodingParametersInterface $parameters)
    {
        return !empty($parameters->getFilters()) ||
            !empty($parameters->getSorts()) ||
            !empty($parameters->getPagination()) ||
            !empty($parameters->getIncludePaths());
    }

}
