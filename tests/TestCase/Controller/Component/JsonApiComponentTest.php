<?php
namespace JsonApi\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
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
        $this->JsonApi = $this->Controller->components()->load('JsonApi.JsonApi');
    }

    /**
     * testInitializeCallback method
     *
     * @return void
     */
    public function testStartupCallback()
    {
        $expectedViewClassMap = [
            'json' => 'Json',
            'xml' => 'Xml',
            'ajax' => 'Ajax',
            'jsonapi' => 'JsonApi.JsonApi'
        ];

        $this->JsonApi->startup(new Event('Controller.startup', $this->Controller));

        $this->assertEquals($expectedViewClassMap, $this->JsonApi->RequestHandler->config('viewClassMap'));
    }

    public function testBeforeRenderCallback()
    {
        $this->JsonApi->config([
            'entities' => [ 'Article' ]
        ]);

        $expected = [
            'url' => null,
            'entities' => [
                'Article'
            ],
            'meta' => [],
            'links' => []
        ];

        $this->JsonApi->beforeRender(new Event('Controller.beforeRender', $this->Controller));

        $viewOptions = $this->Controller->viewBuilder()->options();
        $this->assertEquals($expected, $viewOptions);
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
            'entities' => [ 'Test' ],
            'meta' => [ 'hello' => 'world' ],
            'links' => []
        ];

        $JsonApi = new JsonApiComponent($this->Registry, $settings);

        $this->assertEquals($JsonApi->config('meta'), $settings['meta']);
        $this->assertEquals($JsonApi->config('url'), $settings['url']);
        $this->assertEquals($JsonApi->config('entities'), $settings['entities']);
        $this->assertEquals($JsonApi->config('links'), $settings['links']);
    }

    public function testControllerResponseType()
    {
        $this->JsonApi->config([
            'entities' => [ 'Article', 'Author' ]
        ]);

        $this->Controller->set('_serialize', TableRegistry::get('Articles')->find()->all());

        $this->JsonApi->startUp(new Event('Controller.startup', $this->Controller));
        $this->JsonApi->beforeRender(new Event('Controller.beforeRender', $this->Controller));

        $response = $this->Controller->render();
        $body = $response->body();

        $this->assertEquals('application/vnd.api+json', $response->type());
    }

    public function testControllerResponseData()
    {
        $this->JsonApi->config([
            'url' => 'http://localhost',
            'entities' => [ 'Article', 'Author' ]
        ]);

        $this->Controller->set('_serialize', TableRegistry::get('Articles')->find()->all());

        $this->JsonApi->startUp(new Event('Controller.startup', $this->Controller));
        $this->JsonApi->beforeRender(new Event('Controller.beforeRender', $this->Controller));

        $response = $this->Controller->render();

        $body = $response->body();
        $body = json_decode($body);

        $expected = file_get_contents(ROOT . DS . 'tests' . DS . 'Fixture' . DS . 'articles.json');
        $expected = json_decode($expected);

        $this->assertEquals($expected, $body);
    }

    public function testControllerResponseDataWithMeta()
    {
        $metaData = [ 'meta' => 'data' ];

        $this->JsonApi->config([
            'url' => 'http://localhost',
            'entities' => [ 'Article', 'Author' ]
        ]);

        $this->Controller->set('_serialize', TableRegistry::get('Articles')->find()->all());
        $this->Controller->set('_meta', $metaData);

        $this->JsonApi->startUp(new Event('Controller.startup', $this->Controller));
        $this->JsonApi->beforeRender(new Event('Controller.beforeRender', $this->Controller));

        $response = $this->Controller->render();

        $body = $response->body();
        $body = json_decode($body, true);

        $this->assertArraySubset([ 'meta' => $metaData ], $body);
    }
}
