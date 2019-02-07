<?php

namespace Ttree\JsonApi\Mvc;

use Neos\Flow\Mvc\RequestInterface;

/**
 * Trait AuthorizeRequestTrait
 *
 */
trait AuthorizeRequestTrait
{

    /**
     * @todo authorize
     * Authorize the request.
     *
     * @param AuthorizerInterface $authorizer
     * @param RequestInterface $jsonApiRequest
     * @param $request
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    protected function authorizeRequest(RequestInterface $request, ValidatedRequest $validatdRequest)
    {
        $type = $validatdRequest->getType();

        /** Index */
        if ($validatdRequest->isIndex()) {
            $authorizer->index($type, $request);
            return;
        } /** Create Resource */

        if ($validatdRequest->isCreateResource()) {
            $authorizer->create($type, $request);
            return;
        }

        $record = $jsonApiRequest->getResource();

        /** Read Resource */
        if ($jsonApiRequest->isReadResource()) {
            $authorizer->read($record, $request);
            return;
        } /** Update Resource */
        elseif ($jsonApiRequest->isUpdateResource()) {
            $authorizer->update($record, $request);
            return;
        } /** Delete Resource */
        elseif ($jsonApiRequest->isDeleteResource()) {
            $authorizer->delete($record, $request);
            return;
        }

        $field = $jsonApiRequest->getRelationshipName();

        /** Relationships */
        if ($jsonApiRequest->isReadRelatedResource() || $jsonApiRequest->isReadRelationship()) {
            $authorizer->readRelationship($record, $field, $request);
        } else {
            $authorizer->modifyRelationship($record, $field, $request);
        }
    }
}
