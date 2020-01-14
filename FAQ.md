# FAQ

## What are the steps getting this api to work?
1. Make sure you have a Flow package (with models and relations if needed).
2. Run the `resource:create` command
    2.1 The resource is the singular entity name, lower case (it will automatically take the plural variant)
    2.2 The entity is the full Model class name
    2.3 Copy the part after `...` in your `Settings.yaml`

## How can I make properties show up in the api?
Inside the `getAttributes()` method of the schema class add all the properties you want to disclose via the api inside the array, i.e.:
```php
    public function getAttributes($resource): iterable
    {
        $attributes = [
            'name' => $resource->getName()
        ];

        return $attributes;
    }
```
In the above example the `name` attribute will be disclosed in the api.

## How can I make relations show up in the api?
Inside the `getRelationships()` method of the schema class add the relations you want to disclose, i.e.:
```php
    public function getRelationships($resource): iterable
    {
        $relationships = [
            'movies' => [
                self::RELATIONSHIP_DATA => $resource->getMovies()
            ]
        ];

        return $relationships;
    }
```

# Troubleshooting

## My changes in my `Settings.yaml` don't seem to work
In order for the changes to come through, you will have to force flush the flow caches:
`./flow flow:cache:flush --force`.

## Error: `Adapter ... is not registered`
Make sure your pointer to the adapter class is set correctly inside your `Settings.yaml`.

## Error: `Call to undefined function Flowpack\JsonApi\Schema\_()`
Try force clearing caches.

## Error: `Warning: assert(): No Schema found for resource 'CLASS'`
Define configuration in your `Settings.yaml`.

## Error: `Resource "resource name" related not configured`
The `related` configuration option in `Settings.yaml` is required. If no definition is needed, set it to an
empty array. @see [create issue]
