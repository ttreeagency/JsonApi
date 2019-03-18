<?php

namespace Flowpack\JsonApi\Tests\Functional\Controller;

use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Tests\Functional\Persistence\Fixtures\TestEntity;
use Neos\Flow\Tests\Functional\Persistence\Fixtures\SubEntity;
use Neos\Flow\Tests\Functional\Persistence\Fixtures\TestEntityRepository;
use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Testcase for the JSONAPI Endpoint Controller
 */
class ComplexEndpointControllerTest extends FunctionalTestCase
{
    /**
     * @var boolean
     */
    protected static $testablePersistenceEnabled = true;

    /**
     * @var TestEntityRepository
     */
    protected $testEntityRepository;


    public function setUp()
    {
        parent::setUp();
        if (!$this->persistenceManager instanceof PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }

        $this->testEntityRepository = $this->objectManager->get(TestEntityRepository::class);
    }

    /**
     * @test
     */
    public function fetchRelationships()
    {
        $this->markTestSkipped('fetching Relationships');
    }

    /**
     * @test
     */
    public function fetchRelationshipNotFound()
    {
        $this->markTestSkipped('fetching Relationships');
    }

    /**
     * @test
     */
    public function fetchResourceWithRelatedResourceInclusion()
    {
        $this->markTestSkipped('fetchResourceWithRelatedResourceInclusion');
    }

    /**
     * @test
     */
    public function fetchResourceSparseFieldsets()
    {
        $this->markTestSkipped('fetchResourceWithRelatedResourceInclusion');
    }

    /**
     * @test
     */
    public function fetchResourceListWithPagination()
    {
        $this->markTestSkipped('pagination');
    }

    /**
     * @test
     */
    public function fetchResourceFiltering()
    {
        $this->markTestSkipped('filtering');
    }

    /**
     * @test
     */
    public function createResourceConflict()
    {
        $this->markTestSkipped('conflict');
    }

    /**
     * @test
     */
    public function updateResourceConflict()
    {
        $this->markTestSkipped('conflict');
    }

    /**
     * @test
     */
    public function deleteResourceConflict()
    {
        $this->markTestSkipped('conflict');
    }

    /**
     * @test
     */
    public function fetchRelatedResource()
    {
        $entity = new TestEntity();
        $entity->setName('Andi');
        $relatedEntity = new TestEntity();
        $relatedEntity->setName('Robert');
        $entity->setRelatedEntity($relatedEntity);

        $this->testEntityRepository->add($entity);
        $this->testEntityRepository->add($relatedEntity);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();

        $entityIdentifier = $this->persistenceManager->getIdentifierByObject($entity);
        $response = $this->browser->request('http://localhost/testing/v1/entity-relations/' . $entityIdentifier . '/entities', 'GET');
        $jsonResponse = \json_decode($response->getBody());

        $relatedEntityIdentifier = $this->persistenceManager->getIdentifierByObject($relatedEntity);
        $this->isJson($response->getBody());

        \Neos\Flow\var_dump($jsonResponse);
        $this->assertSame('entities', $jsonResponse->data->type);
        $this->assertSame($relatedEntityIdentifier, $jsonResponse->data->id);
        $this->assertSame('Robert', $jsonResponse->data->attributes->name);
        $this->assertSame('http://localhost/testing/v1/entities/' . $entityIdentifier, $jsonResponse->data->links->self);
    }

    /**
     * @test
     */
    public function fetchRelationshipResource()
    {
        $testEntity = new TestEntity();
        $testEntity->setName('Flow');

        $subEntity1 = new SubEntity();
        $subEntity1->setContent('value');
        $subEntity1->setParentEntity($testEntity);
        $testEntity->addSubEntity($subEntity1);
        $this->persistenceManager->add($subEntity1);

        $subEntity2 = new SubEntity();
        $subEntity2->setContent('value');
        $subEntity2->setParentEntity($testEntity);
        $testEntity->addSubEntity($subEntity2);
        $this->persistenceManager->add($subEntity2);

        $this->testEntityRepository->add($testEntity);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();

        $entityIdentifier = $this->persistenceManager->getIdentifierByObject($testEntity);
        $response = $this->browser->request('http://localhost/testing/v1/entity-relations/' . $entityIdentifier . '/relationships/subentities', 'GET');
        $jsonResponse = \json_decode($response->getBody());

        \Neos\Flow\var_dump($jsonResponse);

    }

    /**
     * @test
     */
    public function updateToOneRelationship()
    {
        $this->markTestSkipped('to one');
    }

    /**
     * @test
     */
    public function updateToManyRelationship()
    {
        $this->markTestSkipped('to many');
    }

    /**
     * @test
     */
    public function validateResource()
    {
        $this->markTestSkipped('validate resource');
    }
}
