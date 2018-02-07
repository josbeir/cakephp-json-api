### !!! THIS PLUGIN IS NOW DEPRECTATED, PLEASE REFER TO [crud-json-api](https://github.com/FriendsOfCake/crud-json-api) for a better alternative

# json-api plugin for CakePHP 3

[![Latest Stable Version](https://poser.pugx.org/josbeir/cakephp-json-api/v/stable)](https://packagist.org/packages/josbeir/cakephp-json-api)
[![Total Downloads](https://poser.pugx.org/josbeir/cakephp-json-api/downloads)](https://packagist.org/packages/josbeir/cakephp-json-api)
[![Latest Unstable Version](https://poser.pugx.org/josbeir/cakephp-json-api/v/unstable)](https://packagist.org/packages/josbeir/cakephp-json-api)
[![License](https://poser.pugx.org/josbeir/cakephp-json-api/license)](https://packagist.org/packages/josbeir/cakephp-json-api)
[![codecov.io](https://codecov.io/github/josbeir/cakephp-json-api/coverage.svg?branch=master)](https://codecov.io/github/josbeir/cakephp-json-api?branch=master)
[![Build Status](https://travis-ci.org/josbeir/cakephp-json-api.svg?branch=master)](https://travis-ci.org/josbeir/cakephp-json-api)

![json:api](http://jsonapi.org/images/jsonapi.png)

This plugin implements [neomerx/json-api](https://github.com/neomerx/json-api) as a View class for cakephp3.

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
composer require josbeir/cakephp-json-api:dev-master
```

## Usage

This plugin works by using [neomerx/json-api](https://github.com/neomerx/json-api) php module at its core, my advice is to read up on the docs before proceeding.

Load the plugin by adding it to your bootstrap.php

```php
Plugin::load('JsonApi');
```

or activate it using the cake shell

```
$ bin/cake plugin load JsonApi
```

Then tell your controller to use the JsonApi view

```php
$this->viewBuilder()->className('JsonApi.JsonApi');
```

The following view variables can be assigned in your controller

| Variable | Description |
| --- | --- |
| `_serialize`| this holds the actual data to pass to the encoder instance, can be an array of entities, a single entity.|
|`_url`| the base url of the api endpoint |
|`_entities`|**required** A list of entities that are going to be mapped to Schemas|
|`_include`| an array of hash paths what should be in the [included](http://jsonapi.org/format/#fetching-includes) section of the response. `[ 'posts.author', 'comments' ]`|
|`_fieldsets`| A hash path of fields should be in the resultset `[ 'sites'  => ['name'], 'people' => ['first_name'] ]` |
|`_meta`| meta data to add to the document |
|`_links`| links to add to the document this should be an array of ``Neomerx\JsonApi\Schema\Link`` objects.|

#### Example

```php
public function initialize()
{
	$this->viewBuilder()->className('JsonApi.JsonApi');

	$this->set('_entities', [
		'Article',
		'Author'
	]);
	
	$this->set('_url', Router::url('/api', true));
	$this->set('_meta', ['some' => 'global metadata']);
	$this->set('_links', [ // uses Neomerx\JsonApi\Schema\Link
		Link::FIRST => new Link('/authors?page=1'),
		Link::LAST => new Link('/authors?page=9', [
			'meta' => 'data'
		])
	]);
}

public function index()
{
	$articles = $this->Articles->find()
		->all();

	$this->set(compact('articles'));
	$this->set('_serialize', true);

	// optional parameters
	$this->set('_include', [ 'articles', 'articles.comments' ]);
	$this->set('_fieldsets', [ 'articles' => [ 'title' ] ]);
}
```

## Schemas

Entities assigned in `_entities` are mapped to the `EntitySchema` base class. This class extends `Neomerx\JsonApi\Schema\SchemaProvider`.

It is **recommended** that you create a schema class for each entity you defined by extending the EntitySchema class. Example: if you have an entity in ``Model\Entity\Author`` then create a schema class in ``View\Schema\AuthorSchema``

Think of the Schema class as a template that represents an Entity.

Because of this it is possible access the current view object along with Request and helpers. ```$this->getView()``` can be called inside the schema if you need it.

### Schema example

Example App\View\Schema\AuthorSchema.php (maps to App\Model\Entity\Author)

```php
<?php
namespace TestApp\View\Schema;

use JsonApi\View\Schema\EntitySchema;

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

## Request handling and routing

This plugin does *not* handle this for you but can be easily added to your application using cake's [RequestHandler](http://book.cakephp.org/3.0/en/controllers/components/request-handling.html) component which has support for the json-api Content-Type.

For instance, if you want to automatically decode incoming json-api *(application/vnd.api+json)* data you can tell RequestHandler to automaticaly handle it.

```php
$this->RequestHandler->config('inputTypeMap.jsonapi', ['json_decode', true]);
```

RESTfull routing can also be achieved by creating [resource routes](http://book.cakephp.org/3.0/en/development/routing.html#creating-restful-routes).

```php
Router::scope('/api', function($routes) {
	$routes->resources('Articles', function($routes) {
		$routes->resources('Authors');
	});
});
```
