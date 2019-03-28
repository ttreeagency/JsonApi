<?php
namespace Flowpack\JsonApi\Tests\Functional;

use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Testcase for resource
 */
class ResourceEndpointTest extends FunctionalTestCase
{
    /**
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var boolean
     */
    protected static $testablePersistenceEnabled = true;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        if (!$this->persistenceManager instanceof PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
        $this->resourceManager = $this->objectManager->get(ResourceManager::class);
    }

    /**
     * @test
     */
    public function fetchResource()
    {
        $resource = $this->resourceManager->importResourceFromContent('fixture', 'fixture.txt');
        $this->assertEquals('fixture', \file_get_contents('resource://' . $resource->getSha1()));

        $entityIdentifier = $this->persistenceManager->getIdentifierByObject($resource);
        $response = $this->browser->request('http://localhost/testing/v1/resources/' . $entityIdentifier, 'GET');
        $jsonResponse = \json_decode($response->getBody());
        $this->isJson($response->getBody());
        $this->assertSame('resources', $jsonResponse->data->type);
        $this->assertSame($entityIdentifier, $jsonResponse->data->id);
        $this->assertSame('fixture.txt', $jsonResponse->data->attributes->filename);
        $this->assertSame('txt', $jsonResponse->data->attributes->{'file-extension'});
        $this->assertSame(7, $jsonResponse->data->attributes->{'file-size'});

        // TODO: Add url check

        $this->assertSame('http://localhost/testing/v1/resources/' . $entityIdentifier, $jsonResponse->data->links->self);
    }

    /**
     * @test
     */
    public function deleteResource()
    {
        $resource = $this->resourceManager->importResourceFromContent('fixture', 'fixture.txt');

        $entityIdentifier = $this->persistenceManager->getIdentifierByObject($resource);
        $response = $this->browser->request('http://localhost/testing/v1/resources/' . $entityIdentifier, 'DELETE');

        $this->assertEquals(204, $response->getStatusCode());
    }
}
