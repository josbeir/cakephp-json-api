<?php
namespace JsonApi\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * JsonApi component
 */
class JsonApiComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'url' => null,
        'schemas' => [],
        'entities' => [],
        'meta' => []
    ];

    /**
     * Initialize config data and properties.
     *
     * @param array $config The config data.
     * @return void
     */
    public function initialize(array $config)
    {
        $controller = $this->_registry->getController();

        $builder = $controller->viewBuilder();
        $builder->className('JsonApi\View\JsonApiView');
        $builder->options($this->config());
    }
}
