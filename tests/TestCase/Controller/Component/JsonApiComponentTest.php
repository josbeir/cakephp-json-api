<?php
namespace JsonApi\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use JsonApi\Controller\Component\JsonApiComponent;

/**
 * JsonApi\Controller\Component\JsonApiComponent Test Case
 */
class JsonApiComponentTest extends TestCase
{

    public $fixtures = [
        'core.articles'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Controller = new Controller(new Request());
        $this->Registry = new ComponentRegistry($this->Controller);
    }

    public function testControllerResponse()
    {
        $articles = TableRegistry::get('Articles')->find()->all();

        $this->Controller->loadComponent('JsonApi.JsonApi', [
            'entities' => [ 'Article' ]
        ]);

        $this->Controller->set('_serialize', $articles);
        $view = $this->Controller->createView();
        $output = $view->render();

        $this->assertSame('application/vnd.api+json', $view->response->type());
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testSettings()
    {
        $settings = [
            'url' => 'http://localhost/',
            'schemas' => [],
            'entities' => [ 'Test' ],
            'meta' => [ 'hello' => 'world' ]
        ];

        $JsonApi = new JsonApiComponent($this->Registry, $settings);

        $this->assertEquals($JsonApi->config('meta'), $settings['meta']);
        $this->assertEquals($JsonApi->config('url'), $settings['url']);
        $this->assertEquals($JsonApi->config('schemas'), $settings['schemas']);
        $this->assertEquals($JsonApi->config('entities'), $settings['entities']);
    }
}
