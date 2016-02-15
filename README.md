# json-api plugin for CakePHP 3

[![Latest Stable Version](https://poser.pugx.org/josbeir/cakephp-json-api/v/stable)](https://packagist.org/packages/josbeir/cakephp-json-api)
[![Total Downloads](https://poser.pugx.org/josbeir/cakephp-json-api/downloads)](https://packagist.org/packages/josbeir/cakephp-json-api)
[![Latest Unstable Version](https://poser.pugx.org/josbeir/cakephp-json-api/v/unstable)](https://packagist.org/packages/josbeir/cakephp-json-api)
[![License](https://poser.pugx.org/josbeir/cakephp-json-api/license)](https://packagist.org/packages/josbeir/cakephp-json-api)
[![codecov.io](https://codecov.io/github/josbeir/cakephp-json-api/coverage.svg?branch=master)](https://codecov.io/github/josbeir/cakephp-json-api?branch=master)
[![Build Status](https://travis-ci.org/josbeir/cakephp-json-api.svg?branch=master)](https://travis-ci.org/josbeir/cakephp-json-api)

![json:api](http://jsonapi.org/images/jsonapi.png)

This plugin implements [neomerx/json-api](https://github.com/neomerx/json-api) for cakephp3.

> JSON API is a specification for how a client should request that resources be fetched or modified, and how a server should respond to those requests.
>
> JSON API is designed to minimize both the number of requests and the amount of data transmitted between clients and servers. This efficiency is achieved without compromising readability, flexibility, or discoverability.
>
> JSON API requires use of the JSON API media type (application/vnd.api+json) for exchanging data.


## Disclaimer

Very much a work in progress. My goal is make it as feature complete as possible but contributions are welcome. Features are added on an occasional basis.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require josbeir/cakephp-json-api
```

## Usage

Load the plugin by adding it to your bootstrap.php

```php
Plugin::load('JsonApi');
```

Load the component

```php
$this->loadComponent('JsonApi.JsonApi', [
	'meta' => [], // global meta
	'links' => [], // global links
	'url' => Router::url('/api', true), // base url of the api
	'entities' => [ // entities that will be mapped to a schema
		'Article',
		'Author'
	]
]);
```

In your controller action (trying to follow known cake concepts) we use **_serialize** to pass our results the **JsonApiView** class (this is where most of the magic happens).

```php
public function index()
{
	$clients = $this->Articles->find()
		->all();

	$this->set('_serialize', $clients);

	// optional parameters
	$this->set('_meta', ['some' => 'meta']);
	$this->set('_include', [ 'articles', 'articles.comments' ]);
	$this->set('_fieldsets', [ 'articles' => [ 'title' ] ]);
	$this->set('_links', [
		Link::FIRST => new Link('/authors?page=1'),
		Link::LAST => new Link('/authors?page=9', [
			'meta' => 'data'
		])
	]);
}
```

## Configuration

This plugin works by using [neomerx/json-api](https://github.com/neomerx/json-api) php module at its core, my advice is to read up on the docs before proceeding.

Instead of configuring the whole mapping by hand the only thing that is required to get going is to define your entity names in the **entities** array when loading the component.

Entities will be mapped to the ``EntitySchema`` base class. This class extends `Neomerx\JsonApi\Schema\SchemaProvider`.

It is recommended that you create a schema for each entity by extending the EntitySchema class. If you have an entity in ``Model\Entity\Author`` then you need to create a schema in ``View\Schema\AuthorSchema``

### Views and schema's

Think of the schema as a template that represents an entity. Because of this it is possible to get access to the current view object, helpers are also available using the views magic accessors just like you would expect in normal templates.

```$this->getView()``` can be called inside the schema if you want to do some fancy view stuff.

### Routing

Cake's built in resource routing should work out of the box, a bit of fine tuning could be needed depending on the type of application you are working on.

```php
$routes->scope('/api', function($routes) {
    $routes->resources('Authors', function($routes) {
        $routes->resources('Articles');
    });
});
```

### Schema example

Example App\View\Schema\AuthorSchema.php (maps to App\Model\Entity\Author)

```php
<?php
namespace TestApp\Schema;

use JsonApi\Schema\EntitySchema;

class AuthorSchema extends EntitySchema
{
    public function getId($entity)
    {
        return $entity->get('id');
    }

    public function getAttributes($entity)
    {
        return [
            'title' => $entity->title,
            'body' => $entity->body,
            'published' => $entity->published,
            'helper_link' => $this->Url->build(['action' => 'view']) // view helper
        ];
    }

    public function getRelationships($entity, array $includeRelationships = [])
    {
        return [
            'articles' => [
                self::DATA => $entity->articles
            ]
        ];
    }
}
```

Will output something like

```json
{
    "data": [
        {
            "type": "authors",
            "id": "1",
            "attributes": {
                "title": null,
                "body": null,
                "published": null
            },
	...
	...

```

## Todo

* Implement a custom resource routing class
* Component option to automatically add pagination metadata
