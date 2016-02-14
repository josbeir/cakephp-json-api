<?php
namespace JsonApi\Schema;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use Cake\View\View;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Schema\SchemaProvider;

class EntitySchema extends SchemaProvider
{
    /**
     * The default field used for an id
     * @var string
     */
    public $idField = 'id';

    /**
     * Holds the instance of Cake\View\View
     * @var Cake\View\View
     */
    protected $_view;

    /**
     * {@inheritdoc}
     *
     * @param Cake\ViewView $view Instance of the cake view we are rendering this in
     * @param string    $entityName Name of the entity this schema is for
     */
    public function __construct(SchemaFactoryInterface $factory, ContainerInterface $container, View $view, $entityName)
    {
        $this->_view = $view;

        if (!$this->resourceType) {
            $this->resourceType = strtolower(Inflector::pluralize($entityName));
        }

        parent::__construct($factory, $container);
    }

    /**
     * Use view helpers like it we normally would
     *
     * {@inheritdoc}
     */
    public function __get($name) {
        return $this->_view->__get($name);
    }

    /**
     * Try to set the correct id for the entity passed
     *
     * {@inheritdoc}
     */
    public function getId($resource)
    {
        return $resource->get($this->idField);
    }

    /**
     * Return all properties of the entity (except the id)
     *
     * {@inheritdoc}
     */
    public function getAttributes($resource)
    {
        if ($resource->has($this->idField)) {
            $resource->hiddenProperties([$this->idField]);
        }

        return $resource->toArray();
    }

    /**
     * Return the view instance
     *
     * @return Cake\View\View View instance
     */
    public function getView()
    {
        return $this->_view;
    }
}
