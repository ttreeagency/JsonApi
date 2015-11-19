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
a custom RoutePartHandler for more flexibility.

How to start you own JSON-API Web Service
-----------------------------------------

First, check the Routing to adapt to your needs, or include the provided [Routes.yaml](Configuration/Routes.yaml) in your
Flow Framework distribution. When you are done with the routing, you can start configuring your service, open your
```Settings.yaml``` and start some definition:

```yaml
  JsonApi:
    endpoints:
      'default':
        baseUrl: '/api/v1/'
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
- ```repository```: Provide a Repository, must implement the ```JsonApiPaginateInterface``` interface, you can use the
```PaginateTrait``` to remove part of the pain
- ```schemas```: Mapping between your models and a schema class, check the [Schema] documentation for more informations

With the given configuration you can open your browser to: http://www.domain.com/api/v1/movies to show the list of 
resource and to http://www.domain.com/movies/d2dc1c34-549f-39a9-c5fc-d716061b0782 to have a single resource.

The Schema class allow you to control the structure of the JSON, create Links, include some resources, ...

Easy ? No ?

Features
--------

- [x] Fetching Resources
- [x] Fetching Resource
- [ ] Fetching Relationships
- [ ] Sorting
- [ ] Sparse Fieldsets
- [ ] Pagination
- [ ] Filtering
- [ ] Support multiple preset
- [ ] Caching

Acknowledgments
---------------

Development sponsored by [ttree ltd - neos solution provider](http://ttree.ch).

We try our best to craft this package with a lots of love, we are open to sponsoring, support request, ... just contact us.

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE.txt) for more information.

[neomerx/json-api]: https://github.com/neomerx/json-api/
[Schema]: https://github.com/neomerx/json-api/wiki/Schemas
[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
