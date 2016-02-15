<?php
namespace JsonApi\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;

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
        'entities' => [],
        'meta' => []
    ];

    /**
     * {@inheritDoc}
     */
    public $components = [
        'RequestHandler'
    ];

    /**
     * Initialize config data and properties.
     *
     * @param  Event  $event [description]
     * @return void
     */
    public function startup(Event $event)
    {
        $this->RequestHandler->config('viewClassMap', [
            'jsonapi' => 'JsonApi.JsonApi'
        ]);
    }

    /**
     * Render the current request as a proper jsonapi response
     *
     * @param  Event  $event Current event_add(event)
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $controller = $event->subject();

        $controller
            ->viewBuilder()
            ->options($this->config());

        $this->RequestHandler->renderAs($controller, 'jsonapi');
    }
}
