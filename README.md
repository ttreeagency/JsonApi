# JSON-API Web Service based on Flow Framework

This package can help you build JSON-API Web Service is a few minutes with Flow Framework.

**This package is under development and not feature complete**

This package is Composer ready, [PSR-2] and [PSR-4] compliant.

Compatible with Flow 2.3.x (we plan to make it compatible with Flow 3.x as soon as our current customer projet upgrade
to Flow 3.x).

How it work ?
-------------

This package integrate [neomerx/json-api] in Flow by providing a API to build JSON-API Web Service by convention. We
try to keep this package simple (KISS) and SOLID as much as we can. We focus on read only API currently.

Routing
-------

Currently the routing is really basic, check [Routes.yaml](Configuration/Routes.yaml). The stable version will contain
custom RoutePartHandlers for more flexibility.

How to start you own JSON-API Web Service
-----------------------------------------

## Create a Resource

The creation of a resource is now easily done with the command `./flow resource:create` the wizard will guide you through the possible options.
It will create a resource with a select class and generate a default `Adapter`, `Schema` and a Functional tes to ensure everything is working properly.

When ready you can extend the schema class with the desired attributes. Add filters to your adapter and start restricting access for example.

## Based on Custom Schema class

You can define a Schema Class to convert your object to a JSON API schema. You can start configuring your service, 
open your ```Settings.yaml``` and start some definition:

```yaml
  JsonApi:
    endpoints:
      'default':
        baseUrl: 'api/v1/'
        version: '0.9.0'
        resources:
          'movies':
            repository: 'Your\Package\Domain\Repository\MovieRepository'
            schemas:
              'Your\Package\Domain\Model\Movie': 'Your\Package\Schema\MovieSchema'
              'Your\Package\Domain\Model\GenericPerson': 'Your\Package\Schema\GenericPersonSchema'
              'Your\Package\Domain\Model\Category': 'Your\Package\Schema\CategorySchema'
```

- Name your preset (default), currently we support only one preset
- ```baseUrl```: The relative base URL of the current preset, must match your Routing
- ```version```: Version of the API, currently not used
- ```resources```: List of Resource to expose in the API
- ```repository```: Provide a Repository, must implement the ```JsonApiRepositoryInterface``` interface, you can use the
```JsonApiRepositoryTrait``` to remove part of the pain
- ```schemas```: Mapping between your models and a schema class, check the [Schema] documentation for more informations

With the given configuration you can open your browser to: http://www.domain.com/api/v1/movies to show the list of 
resource and to http://www.domain.com/movies/d2dc1c34-549f-39a9-c5fc-d716061b0782 to have a single resource.

The Schema class allow you to control the structure of the JSON, create Links, include some resources, ...

Easy ? No ?

## Based on DynamicEntitySchema

You can use this schema build to expose Doctrine entities. The ```DynamicEntitySchema``` use the schema 
definitions stored in your ```JsonApiSchema.yaml```. You can split your definitions in multiple file, 
like ```JsonApiSchema.Movie.yaml```. A basic definition look like this:

```yaml
'Your\Package\Domain\Model\Movie':
  resourceType: 'movies'
  selfSubUrl: '/movies/'

  attributes:
    short_hash:
      property: shortHash
    collection_title:
      property: title.collectionTitle
    local_title:
      property: title.localTitle
    original_title:
      property: title.originalTitle
    teaser:
      property: teaser
    description:
      property: description

  relationships:
    actors:
      data:
        property: directors
        showRelated: TRUE
    directors:
      data:
        property: directors
        showRelated: TRUE

  includePaths:
    directors: TRUE
    actors: TRUE
```

When your Schema definition in YAML is done done, you can configure your endpoint like this:

```yaml
JsonApi:
  endpoints:
    'default':
      baseUrl: 'api/v1/'
      version: '0.9.0'
      resources:
        'movies':
          repository: 'Your\Package\Domain\Repository\MovieRepository'
          
          schemas:
            'Your\Package\Domain\Model\Movie': 'Flowpack\JsonApi\Schema\DynamicEntitySchema'
            'Your\Package\Domain\Model\GenericPerson': 'Your\Package\Schema\GenericPersonSchema'
            'Your\Package\Domain\Model\Category': 'Your\Package\Schema\CategorySchema'
```

## Based on DynamicNodeSchema

You can use this schema build to expose TYPO3CR entities.

[currently not supported, check feature list]

Sorting
-------

By default sorting is disabled, you need to configure explicitly the attributes where sorting is allowed. This 
configuration can be done for each endpoints. In the exemple bellow only the attribute ```title``` is allowed in the
```sort``` request parameter.

```yaml
JsonApi:
  endpoints:
    'default':
      baseUrl: 'api/v1/'
      version: '0.9.0'
      resources:
        'movies':
          repository: 'Your\Package\Domain\Repository\MovieRepository'
          
          sortableAttributes:
            'title': 'title'
            
          schemas:
            'Your\Package\Domain\Model\Movie': 'Flowpack\JsonApi\Schema\DynamicEntitySchema'
            'Your\Package\Domain\Model\GenericPerson': 'Your\Package\Schema\GenericPersonSchema'
            'Your\Package\Domain\Model\Category': 'Your\Package\Schema\CategorySchema'
```

Features
--------

# 1.0

- [x] Fetching Resources
- [x] Fetching Resource
- [x] Fetching Relationships
- [ ] Compound Documents
- [ ] Sparse Fieldsets
- [ ] Schema generation based on YAML definition for Doctrine entites
  - [ ] Property post processors based on EEL during Schema generation
- [x] Sorting
- [x] Pagination (page[number] / page[size] and page[offset] / page[limit])
  - [ ] Configure default pagination strategy, currently hardcode to page[number] & page[size]
- [x] Error Handling - to be improved
- [x] Multiple endpoints
- [x] Resource code generation for adapter and schema
- [ ] Complete test coverage

# 2.0

- [ ] Schema generation based on YAML definition for TYPO3CR nodes
- [ ] Filtering
- [ ] Caching
- [ ] Elastic Search backend

Acknowledgments
---------------

Development sponsored by [REFACTORY - Ambitious Online Solutions](https://rfy.nl) and [flowpack ltd - neos solution provider](http://flowpack.ch).

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE.txt) for more information.

[neomerx/json-api]: https://github.com/neomerx/json-api/
[Schema]: https://github.com/neomerx/json-api/wiki/Schemas
[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
