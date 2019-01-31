# Introduction

Add [jsonapi.org](http://jsonapi.org) compliant APIs to your Flow application.
Based on the framework agnostic packages [neomerx/json-api](https://github.com/neomerx/json-api) and
[rfyio/json-api](https://github.com/rfyio/JsonApi).

## What is JSON API?

From [jsonapi.org](http://jsonapi.org)

> If you've ever argued with your team about the way your JSON responses should be formatted, JSON API is your 
anti-bikeshedding weapon.
>
> By following shared conventions, you can increase productivity, take advantage of generalized tooling, and focus on 
what matters: your application. Clients built around JSON API are able to take advantage of its features around 
efficiently caching responses, sometimes eliminating network requests entirely.

For full information on the spec, plus examples, see [http://jsonapi.org](http://jsonapi.org).

## Demo

We've created a simple [demo application]() that is
available to download, view the code and play around with as needed.

## Theory of Operation

Your application will have one (or many) APIs that conform to the JSON API spec. You define an API in your via routes, 
while JSON API settings are configured in a config file for each API. If you have multiple APIs, each has a unique 
*name*.

A JSON API contains a number of *resource types* that are available within your API. Each resource type
relates directly to a PHP object class. We refer to instances of JSON API resource types as *resources*, and instances 
of your PHP classes as *records*. 

Each resource type has the following units that serve a particular purpose:

1. **Adapter**: Defines how to query and persists records in your application's storage (e.g. database).
2. **Schema**: Serializes a record into its JSON API representation.
3. **Validators**: Provides validator instances to validate JSON API query parameters and HTTP content body.

Although this may sound like a lot of units, our development approach is to use single-purpose units that
are easy to reason about.