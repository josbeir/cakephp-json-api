# json-api plugin for CakePHP 3

[![Latest Stable Version](https://poser.pugx.org/josbeir/cakephp-json-api/v/stable)](https://packagist.org/packages/josbeir/cakephp-json-api) [![Total Downloads](https://poser.pugx.org/josbeir/cakephp-json-api/downloads)](https://packagist.org/packages/josbeir/cakephp-json-api) [![Latest Unstable Version](https://poser.pugx.org/josbeir/cakephp-json-api/v/unstable)](https://packagist.org/packages/josbeir/cakephp-json-api) [![License](https://poser.pugx.org/josbeir/cakephp-json-api/license)](https://packagist.org/packages/josbeir/cakephp-json-api)


![json:api](http://jsonapi.org/images/jsonapi.png)

This plugin implements [neomerx/json-api](https://github.com/neomerx/json-api) for cakephp3

## Disclaimer

Very much a work in progress. My goal is make it as feature complete as possible but contributions are welcome.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require josbeir/cakephp-json-api
```

## Uage

Load the plugin by adding it to your bootstrap.php

```
Plugin::load('JsonApi');
```

Load the component to get going

	$this->loadComponent('JsonApi.JsonApi', [
	    'url' => Router::url('/api', true), // base url of the api
	    'entities' => [ // entities that will be mapped to a schema
	        'Client',
	        'Credential'
	    ]
	]);

## Configuration

This plugin works by using [neomerx/json-api](https://github.com/neomerx/json-api) php module at its core, my advice is to read up on the docs before proceeding.

Instead of configuring the whole mapping by hand the only thing that is required to get you going is to define the **entities** array when loading the component.

Entities will be mapped to the ``EntitySchema`` base class. This class extends `Neomerx\JsonApi\Schema\SchemaProvider`.

The EntitySchema class has access the current view object which greatly increases possibilities inside your cake app (helpers, request, ...)

It is recommended you create a schema for each entitity by extending the EntitySchema class.
If you have an entity in '**Model\Entity\Author.php**' then create a schema in '**Schema\AuthorSchema.php**'

### Routing

Cake's built in resource routing should work out of the box, a bit of fine tuning could be needed depending on the type of application you are working on.

    $routes->scope('/api', function($routes) {
        $routes->resources('Authors', function($routes) {
            $routes->resources('Articles');
        });
    });

### Schema example

Example App\Schema\AuthorSchema.php (maps to App\Model\Entity\Author)

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
	            'helper_link' => $this->Url->buil(['action' => 'view']) // demonstrates helpers
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

Will output something like

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
	            "relationships": {
	                "articles": {
	                    "data": [
	                        {
	                            "type": "articles",
	                            "id": "1"
	                        },
	                        {
	                            "type": "articles",
	                            "id": "3"
	                        }
	                    ]
	                }
	            },
	            "links": {
	                "self": "http:\/\/localhost\/authors\/1"
	            }
	        },
	...
	...

