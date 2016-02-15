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
            'published' => $entity->published
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
